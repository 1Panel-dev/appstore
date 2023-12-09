## 项目介绍

stable-deffusion是基于pytorch的目标检测模型，
当前版本镜像支持GPU，CPU模式需要修改yaml参数，未测试。
GPU最小8G起，4G显存下未测试

安装过程比较漫长，取决于初始模型下载速度，需要耐心等待
安装面板支持apt、pip指令。详细可参考yml文件，当前版本默认安装即可，由于比较消耗资源，限定镜像仅部署1个

本镜像仅做移植，所有功能基于原作者，参考：https://github.com/thirdscam/SD-WebUI-Docker

webui有两个可以选择，都在应用商店里，a1111版和comfy

安装前确保docker默认支持gpu，配置在/etc/docker/daemon.json上改，像这样：
```
{
  "runtimes": {
    "nvidia": {
      "path": "nvidia-container-runtime",
      "runtimeArgs": []
    }
  },
  "default-runtime": "nvidia"
}
```

## 关于缺少依赖的报错
安装镜像过程会自动安装当前版本所需依赖
为适应未来版本变化，添加了apt和pip指令，当前版本不填即可。
当然如果宿主机未安装nvidia-docker-runtime、nvidia-docker2、nvidia-container-toolkit等nvidia容器运行的依赖，可以使用该功能，只需在APT参数上写包名即可，多个包加空格。


## 关于GPU
当前仅设置单GPU，nvdia-smi可以查看gpu的ID，默认0，单显卡默认即可
多显卡可以根据id选择，
如果需要使用所有显卡，需要更改yaml文件，将id设置为all

## 其他
stable-deffusion相关教程：https://www.bilibili.com/video/BV1As4y127HW/?spm_id_from=333.337.search-card.all.click&vd_source=101386edb3f81944f8492e12ee8cb5b6

## api接口
http://localhost:7860/docs/

## 模型下载站
https://civitai.com/