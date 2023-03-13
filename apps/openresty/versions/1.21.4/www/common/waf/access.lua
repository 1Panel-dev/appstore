local match = string.match
local ngxMatch=ngx.re.match
local unescape=ngx.unescape_uri
local get_headers = ngx.req.get_headers
local cjson = require "cjson"
local content_length=tonumber(ngx.req.get_headers()['content-length'])
local method=ngx.req.get_method()


local function optionIsOn(options)
    return options == "on" or options == "On" or options == "ON"
end

local logPath = ngx.var.logdir
local rulePath = ngx.var.RulePath
local PostDeny = optionIsOn(ngx.var.postDeny)

local function getClientIp()
    IP  = ngx.var.remote_addr
    if IP == nil then
        IP  = "unknown"
    end
    return IP
end
local function write(logfile,msg)
    local fd = io.open(logfile,"ab")
    if fd == nil then return end
    fd:write(msg)
    fd:flush()
    fd:close()
end
local function log(method,url,data,ruletag)
    local attackLog = optionIsOn(ngx.var.attackLog)
    if attackLog then
        local realIp = getClientIp()
        local ua = ngx.var.http_user_agent
        local servername=ngx.var.server_name
        local time=ngx.localtime()
        local line = nil
        if ua  then
            line = realIp.." ["..time.."] \""..method.." "..servername..url.."\" \""..data.."\"  \""..ua.."\" \""..ruletag.."\"\n"
        else
            line = realIp.." ["..time.."] \""..method.." "..servername..url.."\" \""..data.."\" - \""..ruletag.."\"\n"
        end
        local filename = logPath..'/'..servername.."_"..ngx.today().."_sec.log"
        write(filename,line)
    end
end
------------------------------------规则读取函数-------------------------------------------------------------------
local function read_json(var)
    file = io.open(rulePath..'/'..var .. '.json',"r")
    if file==nil then
        return
    end
    str = file:read("*a")
    file:close()
    list = cjson.decode(str)
    return list
end

local function select_rules(rules)
    if not rules then return {} end
    new_rules = {}
    for i,v in ipairs(rules) do
        if v[3] == 1 then
            table.insert(new_rules,v[1])
        end
    end
    return new_rules
end

local function read_str(var)
    file = io.open(rulePath..'/'..var,"r")
    if file==nil then
        return
    end
    local str = file:read("*a")
    file:close()
    return str
end

local html=read_str('html')

local function say_html()
    local redirect = optionIsOn(ngx.var.redirect)
    if redirect then
        ngx.header.content_type = "text/html"
        ngx.status = ngx.HTTP_FORBIDDEN
        ngx.say(html)
        ngx.exit(ngx.status)
    end
end

local function whiteUrlCheck()
    local urlWhiteAllow = optionIsOn(ngx.var.urlWhiteAllow)
    if urlWhiteAllow then
        local urlWhiteList = read_json('url_white')
        if urlWhiteList ~= nil then
            for _, rule in pairs(urlWhiteList) do
                if ngxMatch(ngx.var.uri, rule, "isjo") then
                    return true
                end
            end
        end
    end
    return false
end

local function fileExtCheck(ext)
    local fileExtDeny = optionIsOn(ngx.var.fileExtDeny)
    if fileExtDeny then
        local fileExtBlockList = read_json('fileExtBlockList')
        local items = Set(fileExtBlockList)
        ext=string.lower(ext)
        if ext then
            for rule in pairs(items) do
                if ngx.re.match(ext,rule,"isjo") then
                    log('POST',ngx.var.request_uri,"-","file attack with ext "..ext)
                    say_html()
                end
            end
        end
    end
    return false
end
function Set (list)
    local set = {}
    for _, l in ipairs(list) do set[l] = true end
    return set
end

local function getArgsCheck()
    local argsDeny = optionIsOn(ngx.var.argsDeny)
    if argsDeny then
        local argsCheckList=select_rules(read_json('args_check'))
        if argsCheckList then
            for _,rule in pairs(argsCheckList) do
                local uriArgs = ngx.req.get_uri_args()
                for key, val in pairs(uriArgs) do
                    if type(val)=='table' then
                        local t={}
                        for k,v in pairs(val) do
                            if v == true then
                                v=""
                            end
                            table.insert(t,v)
                        end
                        data=table.concat(t, " ")
                    else
                        data=val
                    end
                    if data and type(data) ~= "boolean" and rule ~="" and ngxMatch(unescape(data),rule,"isjo") then
                        log('GET',ngx.var.request_uri,"-",rule)
                        say_html()
                        return true
                    end
                end
            end
        end
    end
    return false
end


local function blockUrlCheck()
    local urlBlockDeny = optionIsOn(ngx.var.urlBlockDeny)
    if urlBlockDeny then
        local urlBlockList=read_json('url_block')
        for _, rule in pairs(urlBlockList) do
            if rule ~= "" and ngxMatch(ngx.var.request_uri, rule, "isjo") then
                log('GET', ngx.var.request_uri, "-", rule)
                say_html()
                return true
            end
        end
    end
    return false
end

function ua()
    local ua = ngx.var.http_user_agent
    if ua ~= nil then
        local uaRules = select_rules(read_json('user_agent'))
        for _,rule in pairs(uaRules) do
            if rule ~="" and ngxMatch(ua,rule,"isjo") then
                log('UA',ngx.var.request_uri,"-",rule)
                say_html()
                return true
            end
        end
    end
    return false
end
function body(data)
    local postCheckList = select_rules(read_json('post_check'))
    for _,rule in pairs(postCheckList) do
        if rule ~="" and data~="" and ngxMatch(unescape(data),rule,"isjo") then
            log('POST',ngx.var.request_uri,data,rule)
            say_html()
            return true
        end
    end
    return false
end
local function cookieCheck()
    local ck = ngx.var.http_cookie
    local cookieDeny = optionIsOn(ngx.var.cookieDeny)
    if cookieDeny and ck then
        local cookieBlockList = select_rules(read_json('cookie_block'))
        for _,rule in pairs(cookieBlockList) do
            if rule ~="" and ngxMatch(ck,rule,"isjo") then
                log('Cookie',ngx.var.request_uri,"-",rule)
                say_html()
                return true
            end
        end
    end
    return false
end

local function denyCC()
    local ccRate = read_str('cc.json')
    local ccDeny = optionIsOn(ngx.var.CCDeny)
    if ccDeny and ccRate then
        local uri=ngx.var.uri
        ccCount=tonumber(string.match(ccRate,'(.*)/'))
        ccSeconds=tonumber(string.match(ccRate,'/(.*)'))
        local access_uri = getClientIp()..uri
        local limit = ngx.shared.limit
        local req,_=limit:get(access_uri)
        if req then
            if req > ccCount then
                ngx.exit(503)
                return true
            else
                limit:incr(access_uri,1)
            end
        else
            limit:set(access_uri,1,ccSeconds)
        end
    end
    return false
end

local function get_boundary()
    local header = get_headers()["content-type"]
    if not header then
        return nil
    end

    if type(header) == "table" then
        header = header[1]
    end

    local m = match(header, ";%s*boundary=\"([^\"]+)\"")
    if m then
        return m
    end

    return match(header, ";%s*boundary=([^\",;]+)")
end

local function whiteIpCheck()
    local ipWhiteAllow = optionIsOn(ngx.var.ipWhiteAllow)
    if ipWhiteAllow then
        local ipWhiteList=read_json('ip_white')
        if next(ipWhiteList) ~= nil then
            for _,ip in pairs(ipWhiteList) do
                if getClientIp()==ip then
                    return true
                end
            end
        end
    end
    return false
end

local function blockIpCheck()
    local ipBlockDeny = optionIsOn(ngx.var.ipBlockDeny)
    if ipBlockDeny then
        local ipBlockList=read_json('ip_block')
        if next(ipBlockList) ~= nil then
            for _,ip in pairs(ipBlockList) do
                if getClientIp()==ip then
                    ngx.exit(403)
                    return true
                end
            end
        end
    end
    return false
end

local function handleBodyKeyOrVal(kv)
    if type(kv) == "table" then
        if type(kv[1]) == "boolean" then
            return
        end
        data = table.concat(kv, ", ")
    else
        data = kv
    end
    if data then
        if type(data) ~= "boolean" then
            body(data)
        end
    end
end

local function postCheck()
    if method == "POST" then
        local boundary = get_boundary()
        if boundary then
            local len = string.len
            local sock = ngx.req.socket()
            if not sock then
                return
            end
            ngx.req.init_body(128 * 1024)
            sock:settimeout(0)
            local contentLength = nil
            contentLength = tonumber(ngx.req.get_headers()['content-length'])
            local chunk_size = 4096
            if contentLength < chunk_size then
                chunk_size = contentLength
            end
            local size = 0
            while size < contentLength do
                local data, err, partial = sock:receive(chunk_size)
                data = data or partial
                if not data then
                    return
                end
                ngx.req.append_body(data)
                if body(data) then
                    return true
                end
                size = size + len(data)
                local m = ngxMatch(data, 'Content-Disposition: form-data; (.+)filename="(.+)\\.(.*)"', 'ijo')
                if m then
                    fileExtCheck(m[3])
                    fileTranslate = true
                else
                    if ngxMatch(data, "Content-Disposition:", 'isjo') then
                        fileTranslate = false
                    end
                    if fileTranslate == false then
                        if body(data) then
                            return true
                        end
                    end
                end
                local less = content_length - size
                if less < chunk_size then
                    chunk_size = less
                end
            end
            ngx.req.finish_body()
        else
            ngx.req.read_body()
            local bodyObj = ngx.req.get_post_args()
            if not bodyObj then
                return
            end
            for key, val in pairs(bodyObj) do
                handleBodyKeyOrVal(key)
                handleBodyKeyOrVal(val)
            end
        end
    end
end

if whiteIpCheck() then
elseif blockIpCheck() then
elseif denyCC() then
elseif ngx.var.http_Acunetix_Aspect then
    ngx.exit(444)
elseif ngx.var.http_X_Scan_Memo then
    ngx.exit(444)
elseif whiteUrlCheck() then
elseif ua() then
elseif blockUrlCheck() then
elseif getArgsCheck() then
elseif cookieCheck() then
elseif PostDeny then
    postCheck()
else
    return
end
