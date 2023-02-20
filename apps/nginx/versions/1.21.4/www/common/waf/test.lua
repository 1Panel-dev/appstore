local cjson = require "cjson"
local rulepath = "rules"

local function read_json(var)
    file = io.open(rulepath..'/'..var .. '.json',"r")
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
        if v[1] == 1 then
            print("111")
            table.insert(new_rules,v[2])
        end
    end
    return new_rules
end



local rules = select_rules(read_json('user_agent'))

for _,v in ipairs(rules) do
    print(v)
end

