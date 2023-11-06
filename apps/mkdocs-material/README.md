<p align="center">
  <a href="https://squidfunk.github.io/mkdocs-material/">
    <img src="https://ghproxy.com/https://raw.githubusercontent.com/squidfunk/mkdocs-material/master/.github/assets/logo.svg" width="320" alt="Material for MkDocs">
  </a>
</p>

<p align="center">
  <strong>
    基于
    <a href="https://www.mkdocs.org/">MkDocs</a> 构建的一个强大的文档框架
  </strong>
</p>

使用 Markdown 编写文档，在几分钟内为您的开源或商业项目创建一个专业的静态网站 —— 可搜索，可定制，支持 60 多种语言，适用于所有设备。

[Github 仓库](https://github.com/squidfunk/mkdocs-material)

[文档地址](https://squidfunk.github.io/mkdocs-material)

# Material for MkDocs 1Panel 版本使用帮助

## UI 展示

![](https://ghproxy.com/https://raw.githubusercontent.com/squidfunk/mkdocs-material/master/.github/assets/screenshot.png)

## 工作目录

本应用会在 1panel 的应用目录生成 mkdocs-material 的工作目录，如果没有改 1panel 的工作目录的话，会是下面这个。

/opt/1panel/apps/local/mkdocs-material

## 项目目录

本应用支持创建多个项目，所以可以有多个项目目录，项目名称为安装时显示的名称。

例如，您创建的项目为 1p-docs，则您可以在 `/opt/1panel/apps/local/mkdocs-material/1p-docs` 中的 docs 目录开始您的撰写。

## 默认端口

默认端口：8111

您可以开启“高级设置 - 端口外部访问”，直接通过 8111 访问该文档项目。或使用反向代理功能，在不开启端口外部访问的情况下，反代这个文档项目。