## 重要说明

> “失败是正常情况，执行重建即可”

### 如果要无 token 使用 Jupyter Notebook

- 一定要重启一次，以便刷新关闭 token 的配置

### 如果要用 token（or 密码） 使用 Jupyter Notebook

- 请到日志中查找，形式如下的链接
- 未设置密码下面的 token 将每次随机生成

```bash
Or copy and paste one of these URLs:
http://0a9217b1dc37:8888/tree?token=228050cf1547825a32c1316d115dd981c1078d262f786e92
http://127.0.0.1:8888/tree?token=228050cf1547825a32c1316d115dd981c1078d262f786e92
```

# Anaconda3

## 介绍

Anaconda3 是一个开源的 Python 发行版，专为数据科学、机器学习和大数据分析而设计。它包含了大量预装的数据科学相关的库，如 NumPy、Pandas、Matplotlib 等，并且集成了一个强大的包管理和环境管理工具 Conda。Anaconda 通过 Conda 可以方便地创建、管理和切换不同的 Python 环境，每个环境可以独立安装不同版本的 Python 及第三方库，解决了多版本库依赖问题。

## Jupyter Notebook

Jupyter Notebook（现在通常称为 JupyterLab）是 Anaconda 套装中的一部分，是一个基于 Web 的交互式计算环境。用户可以在其中编写和运行实时代码（支持多种编程语言，但与 Anaconda 结合时主要使用 Python）、可视化数据、文档化工作流程以及分享结果。Jupyter Notebook 的核心是笔记本文档，这种文档是一个包含代码块（可执行并显示输出）、文本说明（支持 Markdown 格式）、数学公式、图像和其他富媒体内容的混合体。

### 具体特性包括

- 交互性：用户可以逐单元格执行代码，即时查看结果。
- 灵活性：支持超过 40 种编程语言（通过内核系统），包括 Python、R、Julia 等。
- 可重复性：Notebook 能够记录完整的代码执行过程和结果，便于他人复现研究或数据分析步骤。
- 协作与分享：可以通过导出为 HTML、PDF 或其他格式分享给他人，也可以直接在线共享。
- 数据可视化集成：内置支持多种数据可视化库，可以直接在 Notebook 中生成图表和图形。

## 自定义
- 不需要中文可以去docker-compose.yml里删除`pip install jupyterlab-language-pack-zh-CN`