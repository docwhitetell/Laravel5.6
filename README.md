# 基于 Laravel5.6 的后端框架

*   增加Swagger API 文档（）
*   增加短信验证码注册渠道
*   基于laravel/Passport 实现的API登录接口。

## Install 安装
* git clone
* 在根目录下新建 .env文件，复制 .env.example的基础配置到.env中。
修改APP_URL, 数据库连接设置
* 执行 composer install   (安装依赖包)
* 执行 php artisan key:generate  （生成APP_KEY）
* php artisan migrate   (迁移数据表)
* php artisan passport:install 


* 生成 Swagger api文档的命令： <br />
php artisan l5-swagger:generate

* 生成 迁移表命令： <br />
php artisan make:migrate create_xxx_table

* 运行 swoole 命令： <br />
php artisan swoole:http start/stop