支持 Python3、JavaScript、Shell、Typescript 的定时任务管理平台

## 功能

- 支持多种脚本语言（python3、javaScript、shell、typescript）
- 支持在线管理脚本、环境变量、配置文件
- 支持在线查看任务日志
- 支持秒级任务设置
- 支持系统级通知
- 支持暗黑模式
- 支持手机端操作

## 使用

1. 内置命令

```bash
# 更新并重启青龙
ql update                                                    
# 运行自定义脚本extra.sh
ql extra                                                     
# 添加单个脚本文件
ql raw <file_url>                                             
# 添加单个仓库的指定脚本
ql repo <repo_url> <whitelist> <blacklist> <dependence> <branch> <extensions>
# 删除旧日志
ql rmlog <days>                                              
# 启动tg-bot
ql bot                                                       
# 检测青龙环境并修复
ql check                                                     
# 重置登录错误次数
ql resetlet                                                  
# 禁用两步登录
ql resettfa                                                  

# 依次执行，如果设置了随机延迟，将随机延迟一定秒数
task <file_path>                                             
# 依次执行，无论是否设置了随机延迟，均立即运行，前台会输出日，同时记录在日志文件中
task <file_path> now                                         
# 并发执行，无论是否设置了随机延迟，均立即运行，前台不产生日，直接记录在日志文件中，且可指定账号执行
task <file_path> conc <env_name> <account_number>(可选的) 
# 指定账号执行，无论是否设置了随机延迟，均立即运行 
task <file_path> desi <env_name> <account_number>      
# 设置任务超时时间   
task -m <max_time> <file_path>
# 实时打印任务日志，创建定时任务时，不用携带此参数
task -l <file_path>
```

2. 参数说明

* file_url: 脚本地址
* repo_url: 仓库地址
* whitelist: 拉取仓库时的白名单，即就是需要拉取的脚本的路径包含的字符串，多个竖线分割
* blacklist: 拉取仓库时的黑名单，即就是需要拉取的脚本的路径不包含的字符串，多个竖线分割
* dependence: 拉取仓库需要的依赖文件，会直接从仓库拷贝到scripts下的仓库目录，不受黑名单影响，多个竖线分割
* extensions: 拉取仓库的文件后缀，多个竖线分割
* branch: 拉取仓库的分支
* days: 需要保留的日志的天数
* file_path: 任务执行时的文件路径
* env_name: 任务执行时需要并发或者指定时的环境变量名称
* account_number: 任务执行时指定某个环境变量需要执行的账号序号
* max_time: 超时时间，后缀"s"代表秒(默认值), "m"代表分, "h"代表小时, "d"代表天

## 开发

```bash
$ git clone git@github.com:whyour/qinglong.git
$ cd qinglong
$ cp .env.example .env
# 推荐使用 pnpm https://pnpm.io/zh/installation
$ npm install -g pnpm@8.3.1
$ pnpm install
$ pnpm start
```

打开你的浏览器，访问 http://127.0.0.1:5700

## 名称来源

青龙，又名苍龙，在中国传统文化中是四象之一、[天之四灵](https://zh.wikipedia.org/wiki/%E5%A4%A9%E4%B9%8B%E5%9B%9B%E7%81%B5)之一，根据五行学说，它是代表东方的灵兽，为青色的龙，五行属木，代表的季节是春季，八卦主震。苍龙与应龙一样，都是身具羽翼。《张果星宗》称“又有辅翼，方为真龙”。

《后汉书·律历志下》记载：日周于天，一寒一暑，四时备成，万物毕改，摄提迁次，青龙移辰，谓之岁。

在中国[二十八宿](https://zh.wikipedia.org/wiki/%E4%BA%8C%E5%8D%81%E5%85%AB%E5%AE%BF)中，青龙是东方七宿（角、亢、氐、房、心、尾、箕）的总称。 在早期星宿信仰中，祂是最尊贵的天神。 但被道教信仰吸纳入其神系后，神格大跌，道教将其称为“孟章”，在不同的道经中有“帝君”、“圣将”、“神将”和“捕鬼将”等称呼，与白虎监兵神君一起，是道教的护卫天神。
