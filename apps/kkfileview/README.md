# kkFileView

文档在线预览项目解决方案，项目使用流行的spring boot搭建，易上手和部署。万能的文件预览开源项目，基本支持主流文档格式预览，如：

1. 支持 doc, docx, xls, xlsx, xlsm, ppt, pptx, csv, tsv, dotm, xlt, xltm, dot, dotx,xlam, xla ,pages 等 Office 办公文档
2. 支持 wps, dps, et, ett, wpt 等国产 WPS Office 办公文档
3. 支持 odt, ods, ots, odp, otp, six, ott, fodt, fods 等OpenOffice、LibreOffice 办公文档
4. 支持 vsd, vsdx 等 Visio 流程图文件
5. 支持 wmf, emf 等 Windows 系统图像文件
6. 支持 psd ,eps 等 Photoshop 软件模型文件
7. 支持 pdf ,ofd, rtf 等文档
8. 支持 xmind 软件模型文件
9. 支持 bpmn 工作流文件
10. 支持 eml 邮件文件
11. 支持 epub 图书文档
12. 支持 obj, 3ds, stl, ply, gltf, glb, off, 3dm, fbx, dae, wrl, 3mf, ifc, brep, step, iges, fcstd, bim 等 3D 模型文件
13. 支持 dwg, dxf, dwf, iges , igs, dwt, dng, ifc, dwfx, stl, cf2, plt 等 CAD 模型文件
14. 支持 txt, xml(渲染), xbrl(渲染), md(渲染), java, php, py, js, css 等所有纯文本
15. 支持 zip, rar, jar, tar, gzip, 7z 等压缩包
16. 支持 jpg, jpeg, png, gif, bmp, ico, jfif, webp 等图片预览（翻转，缩放，镜像）
17. 支持 tif, tiff 图信息模型文件
18. 支持 tga 图像格式文件
19. 支持 svg 矢量图像格式文件
20. 支持 mp3,wav,mp4,flv 等音视频格式文件
21. 支持 avi,mov,rm,webm,ts,rm,mkv,mpeg,ogg,mpg,rmvb,wmv,3gp,ts,swf 等视频格式转码预览
22. 支持 dcm 等医疗数位影像预览
23. 支持 drawio 绘图预览

> 基于当前良好的架构模式，支持的文件类型在进一步丰富中

### 项目特性

- 使用 spring-boot 开发，预览服务搭建部署非常简便
- rest 接口提供服务，跨语言、跨平台特性(java,php,python,go,php，....)都支持，应用接入简单方便
- 抽象预览服务接口，方便二次开发，非常方便添加其他类型文件预览支持
- 最最重要 Apache 协议开源，代码 pull 下来想干嘛就干嘛