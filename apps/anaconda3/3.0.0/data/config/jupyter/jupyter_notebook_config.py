##from notebook.auth import passwd
##
##c = get_config()
### 如果希望使用密码登录，则加上下面这行：
###c.NotebookApp.password = passwd('${JUPYTER_PASSWORD}')
### 如果希望禁用token登录，则加上下面这行：
c.NotebookApp.token = ''
##c.ServerApp.password = passwd('${JUPYTER_PASSWORD}')