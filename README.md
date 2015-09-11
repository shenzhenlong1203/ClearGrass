# ClearGrass.com Homepage Project

> This project is simply for the show the homepage of ClearGrass Inc., now.

> User commits an email for subscribing the news of progresses.


# 开发注意事项

* 增加了配置文件 在config下面，本地开发时的配置写到cfg.local.ini下面即可, 取配置的方法为 getConfig()
* html页面里引资源时，用 cdnPath($path)，例如 cdnPath('/images/logo.png')
* 服务器上的配置文件单独存储了，不会提交，本地不用管
* 静态文件放在 public/static 目录下即可，这个目录下的文件会关联到cdn