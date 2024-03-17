#!/bin/bash

jupyter_notebook_config="data/config/jupyter/jupyter_notebook_config.py"

# 检查环境变量是否被定义
if [ -z "${USING_PASSWORD}" ] || [ -z "${JUPYTER_PASSWORD}" ]; then
    echo "环境变量 USING_PASSWORD 和 JUPYTER_PASSWORD 必须被定义。"
    exit 1
fi

### 将重复代码提取到函数中
##configure_jupyter_notebook_config() {
##    echo "from notebook.auth import passwd" > ${jupyter_notebook_config}
##    echo "c = get_config()" >> ${jupyter_notebook_config}
##}

# 使用 if-else 结构简化逻辑
if [ "${USING_PASSWORD}" == "true" ]; then
    #configure_jupyter_notebook_config
    # 使用密码进行配置
    echo "c.NotebookApp.password = passwd('${JUPYTER_PASSWORD}')">> ${jupyter_notebook_config}
    #echo "c.NotebookApp.password = '${JUPYTER_PASSWORD}'" >> ${jupyter_notebook_config}
else
    #configure_jupyter_notebook_config
    # 如果希望禁用token登录，则加上下面这行：
    echo "c.NotebookApp.token = ''">> ${jupyter_notebook_config}
fi

# 确认配置文件已更新
echo "Jupyter Notebook 配置已更新。"

