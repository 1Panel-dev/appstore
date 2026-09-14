## 使用说明

适合成长型团队的最快知识库，美观、实时协作、功能丰富且支持 Markdown 格式。

此知识库没有自己的登录门户，需要专门的 OAuth 应用支持登录，安装前请先阅读下面的 GitHub OAuth 申请说明（以 GitHub OAuth 为例）。

### 一、GitHub OAuth 申请

1. 访问 [GitHub](https://github.com/) 并登录
2. 进入 [OAuth Apps](https://github.com/settings/developers) 页面（也可以依次点击：右上角头像 - `Settings` - `Developer Settings` - `OAuth Apps`）
3. 点击 `New OAuth App`
4. 填写 `Register a new OAuth application` 表单：
    - `Application name`: 可自行填写，例如 `outline`
    - `Homepage URL`: 填写 `Outline` 的根路径 `URL`，例如 `http://your-domain.com` 或者 `http://<你的服务器IP>:<访问端口>`。
    - `Authorization callback URL`: 填写 `<Homepage URL>/auth/oidc.callback`，其中 `<Homepage URL>` 需要替换为 `Outline` 的根页面 `URL`，例如 `http://your-domain.com/auth/oidc.callback` 或者 `http://<你的服务器IP>:<访问端口>/auth/oidc.callback`。
5. 点击 `Register application` 按钮。

接下来获取 `Client ID` 和 `Client secrets`：

1. 进入 `OAuth Apps` 页面（也可以依次点击：右上角头像 - `Settings` - `Developer Settings` - `OAuth Apps`）
2. 选择上面创建的应用
3. 点击 `Generate a new client secret` 按钮
4. 记下 `Client ID` 和 `Client secret`（注意 `Client secret` 仅在创建时显示一次，后续不可再查询；如不慎遗失，可以再次点击按钮重新创建一个）

> 然后在安装 `Outline` 时，将 `Client ID` 和 `Client secret` 填写到：
> - `GitHub OAuth 客户端 ID`
> - `GitHub OAuth 密钥`

### 二、反向代理域名

这个是可选项，如果你没有绑定域名，推荐直接填写这个格式的地址 `http://<你的服务器IP>:<访问端口>` 来访问 Outline 管理后台。

如果你配置了反向代理域名，请确保你的反向代理已经正确配置并指向了 Outline 所在服务器的访问端口，否则你将无法通过域名访问 Outline 。

### 三、SMTP 邮件服务配置（可跳过）

如果你希望通过邮件邀请成员加入 Outline 知识库，或者重置密码等操作，你需要配置 SMTP 邮件服务。

请在安装界面填写正确的 SMTP 邮件服务信息。如果你没有 SMTP 邮件服务，可以使用一些免费的 SMTP 服务提供商，或者使用你自己的邮箱服务商提供的 SMTP 服务。

## 四、登录

安装完成之后，请访问 `http://<你的服务器IP>:<访问端口>` 来登录 Outline，正常的话会自动跳转到 GitHub 的授权页面，授权成功后会跳转回 Outline 知识库首页。

> 如果没有跳转可以点击 `使用 GitHub 继续` 按钮。

## 建议

勾选 "端口外部访问" 以便通过反向代理或直接通过 IP 访问 Outline。
