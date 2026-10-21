# 定时任务（cron）部署说明

> 适用版本：9.10.5（9.8.3 业务内核 + 10.1 界面）
> 更新日期：2026-10-21

## 当前状态

dev.wenyinos.org 已关闭计划任务：`zt_config` 中 `system/common/global/cron = 0`。
页面页脚不再执行 `startCron()`，`/cron-ajaxExec-*` 不再被请求，499 与常驻 PHP-FPM 进程随之消失。

关闭后以下任务不会执行：

- 每日 00:30 数据/附件/代码备份
- 每 5 分钟异步发信、Webhook 异步投递
- errorlog 超期清理
- 每日 23:30 燃尽图、08:00 任务提醒

## 为什么不能直接打开后台开关

`module/cron/control.php::ajaxExec()` 是设计为最长 8 天的常驻循环（`set_time_limit(0)` + `ignore_user_abort(true)`）。
页面每次加载都会调用 `startCron()`，发一个 **100ms** 超时的 AJAX 请求；浏览器很快断开，但服务端继续占用
PHP-FPM 进程，直到 `request_terminate_timeout`（本机 100s）将其 SIGTERM。结果是大量 499、FPM 池被占用，
长任务（如备份）还可能在中途被杀。

## 恢复计划任务（推荐）

1. 后台保持计划任务开关打开（`system/common/global/cron = 1`）。
2. 在 nginx 站点 `server` 块中阻断浏览器触发（放在通用 location 之前）：

   ```nginx
   location ~ ^/cron-ajaxExec- {
       return 204;
   }
   ```

3. 只运行一个 CLI 常驻进程（二选一）：

   ```ini
   # /etc/systemd/system/zentao-cron.service
   [Unit]
   Description=ZenTao cron daemon
   After=network-online.target

   [Service]
   User=www
   Group=www
   WorkingDirectory=/www/wwwroot/dev.wenyinos
   ExecStart=/www/server/php/85/bin/php /www/wwwroot/dev.wenyinos/bin/ztcli 'http://127.0.0.1/cron-ajaxExec-0.html'
   Restart=always
   RestartSec=60

   [Install]
   WantedBy=multi-user.target
   ```

   或使用 `/etc/cron.d/zentao-cron` + `flock`，避免每分钟叠加常驻进程：

   ```cron
   * * * * * www /usr/bin/flock -n /tmp/zentao-cron.lock /www/server/php/85/bin/php /www/wwwroot/dev.wenyinos/bin/ztcli 'http://127.0.0.1/cron-ajaxExec-0.html' >/dev/null 2>&1
   ```

4. 验证：

   - `ps -ef | grep '[c]ron-ajaxExec'` 只有一个 `www` 进程
   - `zt_cron` 首条“监控定时任务”的 `lastTime` 每分钟推进
   - `tmp/log/cron.*.log.php` 出现带 command 的任务日志

## 注意

- `bin/ztcli` 只接受一个 URL 参数；本仓库默认 `requestType=PATH_INFO`，应使用
  `.../cron-ajaxExec-0.html`，不要使用 `bin/ztcli cron` 或 `index.php?m=cron&f=ajaxExec`。
- `ajaxExec` 入口没有 `runable()` 检查，`* * * * *` 直接调用会叠加常驻进程；必须用 systemd 或 `flock -n`。
- nginx 阻断后，计划任务页的“重启”按钮不再生效，重启请用 `systemctl restart zentao-cron`。
- 若关闭计划任务开关，同时停用 CLI 守护进程，避免其每分钟空转重启。
