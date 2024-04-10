# 使用说明

## 安装好后，我的笔记数据在哪？

为了防止您更新/删除思源笔记导致的数据丢失问题，默认笔记数据将放置在应用安装目录的 ./data/ 的文件夹下。

## 我该如何修改访问授权码？

请您在【应用商店】内【已安装】选项卡中找到思源笔记，然后点击参数，选择编辑且修改访问授权码，保存即可。

## 网页版和客户端有什么区别？

网页版和客户端相比，局限性在于：
1. 不支持桌面和移动应用程序连接，仅支持在浏览器上使用
2. 不支持导出为 PDF、HTML 和 Word 格式
3. 不支持导入 Markdown 文件

以上内容来自[Docker Siyuan](https://hub.docker.com/r/b3log/siyuan)，如果你需要以上部分或全部内容，请使用客户端。

## 如何隐藏端口

首先先关闭思源笔记的【端口外部访问】功能，然后在左侧【网站】选项卡内找到【创建网站】，选择【反向代理】功能，最后填写上主域名和代理地址（HTTP）保存即可。

其中主域名是您可以正常访问的域名，如：siyuan.mydomain.com。
代理地址中的端口是您在前面配置的思源端口号，如：127.0.0.1:6806。

如果您使用 NGINX 进行反向代理，您可能还需要手动配置下 WebSocket 反代 /ws 域名。你可以参考以下配置：

```conf
location /ws {
    proxy_pass http://127.0.0.1:6806;
    proxy_read_timeout 60s;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection 'Upgrade';
}
```