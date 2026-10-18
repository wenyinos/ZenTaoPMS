# 开源社区项目管理与辅助开发功能实施方案

> 适用基线：WenYinOS ZenTaoPMS 9.10.x（9.8.3 核心冻结 + 10.1 界面 + 22.6 移植线）
> 目标读者：本仓库维护者
> 文档性质：功能选型与实施方案，未含代码改动；实施前按功能拆分为独立分支，每个功能单独评审。

---

## 0. 结论速览（优先级总表）

调研对象：GitLab CE、Gitea、Redmine、OpenProject、Taiga、Plane、禅道上游 12.x/18.x/22.6（开源版功能集），结合本仓库现状（`module/` 实有 55 个模块 + 22.6 移植线已交付的 pivot 透视表 / errorlog 错误日志 / mcp 接口 / 看板升级 / 年度总结 / 需求矩阵 / CSV 批量导入导出 / 文档增强）。

| 优先级 | 编号 | 功能 | 一句话价值 | 预估工作量 |
|---|---|---|---|---|
| **P0** | F1 | 代码平台 Webhook 接入（GitHub/GitLab/Gitea） | push/MR/issue 事件实时联动对象状态，补齐现有 cron 拉取式的时效短板 | 60 人时 |
| **P0** | F2 | CI/CD 集成（job/pipeline 精简版） | 构建状态回写 build/release，开源项目最核心的"开发辅助" | 80 人时 |
| **P0** | F3 | 通知渠道扩展（企业微信/飞书/Discord） | webhook 已内置钉钉/倍洽，仅缺 3 个渠道模板，投入产出比最高 | 30 人时 |
| **P1** | F4 | 代码评审精简版（review） | MR 级评论 + 结论回写，不做行内 diff 批注 | 70 人时 |
| **P1** | F5 | 贡献者统计与社区健康报表 | 复用已移植的 pivot 引擎，纯配置层增量 | 25 人时 |
| **P1** | F6 | 公共 Roadmap 与 Changelog 生成 | 从 productplan/release 自动生成对外页面与发布说明 | 40 人时 |
| **P2** | F7 | Wiki 知识库（doc 模块增强） | 不做新模块，给 doc 加"公开空间 + 版本历史导出" | 50 人时 |
| **P2** | F8 | 社区反馈门户（feedback 精简版） | 匿名反馈入口 + 一键转 bug/story，开源项目接收社区声音的标配 | 60 人时 |
| **P2** | F9 | 甘特图（project 视图增强） | eCharts 已批准引入（移植线决策 1），纯前端增量 | 35 人时 |
| **P3** | F10 | OpenAPI 文档输出 | 从 api/mcp 路由表机械生成，服务第三方集成 | 30 人时 |
| **P3** | F11 | 站会/会议纪要（todo 增强） | 轻量复用 todo + doc，不做独立会议模块 | 25 人时 |
| **P3** | F12 | README 徽章服务（badge endpoint） | open bugs / release version SVG，一张图的成本 | 15 人时 |

> ⚠ 上表为**初版调研排序**；经复核的**实施建议**（含应做 / 裁剪 / 不做 / 推迟的分级与首批计划）见 **§9**。二者冲突时以 §9 为准。

**明确不做**（调研后否决）：
- 完整 Git 托管（repo 浏览器/在线 diff）：上游 22.6 的 repo/mr 模块依赖 zin 前端与 bi 体系，与本仓库"9.8.3 核心 + 10.1 界面"约束冲突；代码浏览交给 Gitea/GitHub，本系统只做**联动**不做**托管**。
- 即时聊天/论坛：与项目管理工具定位不符，社区讨论由 GitHub Discussions 等承载。
- 完整 DevOps 流水编排（上游 22.6 的 pipeline 编排器）：工作量 200+ 人时且强依赖 zin，本期只做"状态回写"级集成。

---

## 1. 现状盘点（已有能力 → 缺口）

### 1.1 已有（不要重复造）
- **git/svn 模块**：cron 拉取提交日志 → `parseComment` 解析注释 → `saveAction2PMS` 关联 story/task/bug。**拉取式**，延迟 = cron 周期，且不支持 MR/issue 事件。
- **webhook 模块**：出站通知（`zt_webhook` + `zt_webhookdatas`），已内置**钉钉 / 倍洽 / 默认**三种渠道（`model.php::getDingdingData` / `getBearychatData`），支持 sync/async 两种发送模式；缺企业微信 / 飞书 / Discord。
- **mcp 模块**：AI 客户端接口已覆盖 task/story/bug/project/product/plan/release/testcase/doc 的查、建、流转、工时（`module/mcp/control.php` 50+ 方法），是"辅助开发"的现成地基。
- **pivot 模块（22.6 移植）**：只读透视引擎 + `config/pivot.php::$config->pivot->builtins` 内置报表 upsert 机制（现有 5 张：bugsassign / bugscreate / projectdeviation / productsummary / workload）。
- **errorlog / cron / message / mail / score**：运行支撑齐备。

### 1.2 缺口（对照开源社区工作流）
| 社区工作流场景 | 现状缺口 |
|---|---|
| 贡献者 push/MR 后任务自动流转 | 只有 cron 拉 git log，无 Webhook 接收端 |
| CI 构建失败 → 自动建 bug / 通知 | 无 CI 集成 |
| 发版时生成对外 Changelog | release 模块只有内部字段，无对外渲染 |
| 路人甲提交反馈（无账号） | 无匿名入口，bug 创建需登录 |
| 对外展示路线图 | productplan 仅登录可见 |
| 企微/飞书/Discord 通知 | webhook 已有钉钉/倍洽，其余渠道模板缺失 |
| 评审意见沉淀 | 无 review 对象，评审散落在 action 评论里 |

---

## 2. P0 功能详案

### F1 代码平台 Webhook 接入 —— 60 人时

**目标**：新增 `module/repohook/`（或按评审意见并入 `module/webhook` 作为入站方向），接收 GitHub / GitLab / Gitea 的 push、merge_request/pull_request、issues 事件，写入对象 action 记录，并**新增**（现有拉取式不具备的）状态流转能力。

**实施方案**：
- **数据模型**（幂等升级，遵守 §6 升级约定）：
  - `zt_repohook`：id, provider(github/gitlab/gitea), secret, product, events(JSON), status, createdDate。
  - `zt_repohooklog`：id, hookID, event, payload(text), result, createdDate。排障必备，写入失败**吞掉异常**（沿用 errorlog 的设计纪律）。
- **路由**：走 `www/api.php` 匿名路由 + `module/entry` 授权体系（`zt_entry`：code / key / IP 白名单 / 访问日志），新增 `repohook-receive-<token>` 入口，在 entry 的 key 校验之外再叠加平台 HMAC 签名（GitHub `X-Hub-Signature-256` / GitLab `X-Gitlab-Token` / Gitea 同 GitHub）。注：仓库内**没有** `www/entry.php`，匿名入口统一由 `api.php` 分发。
- **事件映射**（首版只做三件套，不做评论/label 同步）：
  - push → 复用 `git->parseComment` 提取对象编号（`story #123`、`bug #456`、`task #789`，支持 `#` / `:` / `：` 分隔与逗号/空格列表）+ `git->saveAction2PMS` 写 `gitcommited` action，与现有拉取式**同一解析函数**，保证两种来源行为一致。⚠ **注意**：现有 `parseComment` **只提取 ID、不解析动词**，`saveAction2PMS` **只写 action、不改对象状态**；"push 即流转状态"（如 `fixed` / `finish` 触发 resolve/finish）属**新增逻辑**，需自行实现动词识别，不能靠复用现有函数得到。
  - MR 合并 → 解析源分支名/标题中的对象编号，调用对应模型方法批量流转（**workflow 执行逻辑为新增**，现有 git/svn 无此能力）。
  - issue 创建（可选开关）→ 自动建 bug 并在 action 中回链平台 issue URL。
- **UI**：admin 后台列表 + 创建表单（10.1 骨架，`#pageNav` 不涉及）。显示最近 50 条 hooklog。
- **与现有 git/svn 模块关系**：并存。拉取式留给内网无外网回调的场景；Webhook 留给公网托管场景。
- **验收**：Gitea 本地实例 push 含 `bug #1 fixed` 的提交，5 秒内 bug 上出现 `gitcommited` action 且含 commit 链接（若实现了动词识别，则同时触发状态变更）；伪造签名的请求返回 403 且不产生任何写库。

**风险**：匿名入口是攻击面 → 强制签名校验 + IP 白名单字段 + 全量 hooklog。回退 = 关闭 hook 开关，不影响拉取式。

### F2 CI/CD 集成（job 精简版）—— 80 人时

**目标**：对接 GitHub Actions / GitLab CI / Jenkins，构建结果回写 `zt_build` 并在失败时可自动建 bug。**不做**流水线编排器。

**实施方案**：
- **数据模型**：
  - `zt_ciengine`：id, name, type(github/gitlab/jenkins), baseUrl, token（加密存储，不进 release tarball 的 my.php 之外任何文件）, status。
  - `zt_cijob`：id, engine, product, repo 标识, triggerType(tag/commit/api), lastStatus, lastTime, lastUrl。
  - 构建记录复用 `zt_action` + `zt_history`（objectType=ciJob），不新建大表。
- **两条通路**：
  1. **轮询**（cron 模块注册 `ci-refresh`）：每 10 分钟拉一次各 engine 的流水线状态，适合内网。
  2. **回推**：复用 F1 的 Webhook 接收端，GitHub `workflow_run` / GitLab `Pipeline Hook` 事件直达。
- **回写动作**：构建成功 → 若存在同名 build 则更新状态；构建失败 → 按 job 配置可选自动建 bug（title=流水线名+run编号，assignedTo=job 负责人，steps 里带 run URL）。
- **UI**：qa/build 视图旁挂"构建"标签页；产品 dashboard 加 block（block 模块已有扩展机制）。
- **验收**：GitHub Actions 一次失败 run → 10 分钟内（轮询）或 5 秒内（回推）系统内出现对应 bug，且 bug 描述含可点击的 run 链接。

**风险**：各平台 API 分页/限流差异 → engine 层做适配接口 `getLatestRuns($job)`，每平台一个实现类，单类 ≤300 行。回退 = 停 cron，删 zt_cijob 配置即可。

### F3 通知渠道扩展 —— 30 人时

**目标**：webhook 模块新增 3 个渠道模板：企业微信机器人、飞书机器人、Discord。**钉钉与倍洽已内置**（`module/webhook/model.php::getDingdingData` / `getBearychatData`，钉钉的加签与 markdown 卡片逻辑可直接参照）。全部走"加签/密钥 + markdown 卡片"的最简形态。

**实施方案**：
- 渠道 type 在 `module/webhook/lang/zh-cn.php` 的 `$lang->webhook->typeList` 注册（现有 `bearychat` / `dingding` / `default`），发送分支在 `module/webhook/model.php::buildData()` 扩展（现有 `if($webhook->type == 'dingding') … elseif($webhook->type == 'bearychat')`）；企微/飞书两家 markdown 消息体高度相似，共享一个构造器 + 渠道差异表。
- 企微/飞书的加签：timestamp + HmacSHA256(secret, timestamp) 拼接进 URL，注意**钉钉签名串含换行**（钉钉实现已有，可直接参照），单测必须覆盖。
- 发送失败重试沿用现有队列，不改队列结构。
- **验收**：四种渠道各发一条 bug 指派通知，群内有卡片且点击跳转系统内 bug 页。

---

## 3. P1 功能详案

### F4 代码评审精简版 —— 70 人时

**目标**：不做行内 diff 批注（那是代码托管平台的活），只做"MR 级评审记录"：评审人、结论（通过/驳回/需修改）、意见文本、关联 MR URL。

- **数据模型**：`zt_review`：id, product, repoURL, mrIID, title, reviewer, result, comment, source(webhook/manual), createdDate。结论变更写 action。
- **来源**：F1 的 MR 事件自动建档（`opened` 建待评审记录，`merged/closed` 自动终态）+ 手工补录入口。
- **联动**：评审通过且 MR 标题含对象编号 → 触发与 F1 相同的状态流转；驳回 → 给 MR 作者创建 todo。
- **验收**：Gitea MR 从创建到合并，系统内 review 记录状态同步，关联 task 完成，评审意见可在 review 详情页查看。

### F5 贡献者统计与社区健康报表 —— 25 人时

**目标**：纯 pivot 内置报表增量，零新模块。

在 `config->pivot->builtins` 追加（按 code upsert，不覆盖用户改过的行——遵守既有机制）：
1. **贡献者排行**：按 action 表统计周期内各账号的操作数/完成对象数。
2. **外部贡献识别**：dept 为空或标记为外部的账号单独成组。
3. **社区健康**：bug 平均关闭时长、story 吞吐量趋势、review 响应时长（依赖 F4）。
4. **版本质量**：每个 release 关联 bug 的新增/关闭曲线（eCharts 已由移植线引入，直接复用）。

**验收**：pivot preview/show 中可见上述报表；重跑 upgrade 后用户手改过的报表不被覆盖。

### F6 公共 Roadmap 与 Changelog 生成 —— 40 人时

**目标**：两个匿名只读页面 + 一个生成器。

- **Roadmap 页**：`/roadmap.php`（`api.php` 匿名路由 + `module/entry` 授权，可配置开关）：渲染 productplan 中标记为 `public` 的计划 + 各 story 进度聚合（不暴露指派人、工时等内部字段）。
- **Changelog 生成器**：release 视图增加"生成发布说明"按钮 → 按 release 关联的 story/bug 分组渲染 markdown（模板可编辑），输出与 `doc/release-notes/` 同构的文本，可直接贴进 GitHub Release。模板分组规则：closedStory / resolvedBug / 遗留 knownIssues。
- **数据模型**：`zt_productplan` 加 `isPublic` tinyint（幂等 ALTER，先探 information_schema）。
- **验收**：匿名访问 roadmap 页只见 public 计划；生成的 changelog 与人工维护的 9.10.x release-notes 格式对齐。

---

## 4. P2 功能详案

### F7 Wiki 知识库（doc 增强）—— 50 人时
- 不新建模块。给 doc 的 lib 加 `type=wiki` + 公开开关；文档加历史版本导出（zt_doccontent 已有版本链，只缺导出 zip/md 的 action）。
- ⚠ **隐藏成本**：doclib 已有 `type='template'` 先例，`module/doc/model.php` 中 **8 处以上** `->andWhere('type')->ne('template')` 的库列表查询（`getLibs` 家族）都需同步排除 `wiki`，否则公开 wiki 库会漏进普通库列表。改动机械但面广，务必逐处核对。
- 公开 wiki 页走 `api.php` 匿名路由（同 F6），与 roadmap 共用访问控制开关。

### F8 社区反馈门户（feedback 精简版）—— 60 人时
- `zt_feedback`：id, title, content, contact(选填), product, status(pending/reviewed/converted), createdObject, createdDate。
- 匿名提交表单（频率限制 / IP 节流——**必做**，否则是垃圾入口）。⚠ 本仓库**没有**通用表单验证码机制（`captcha` 仅出现在 `module/admin` 对接禅道官方账号的注册 / 认证页，不适用于本站表单）；如需图形验证码须自行引入，否则以频率限制替代。
- 后台一键转 bug/story：预填标题描述，feedback 记录转化去向。
- 转化后若留了邮箱，对象状态变更时经 mail 模块通知反馈人（模板文本可配置）。

### F9 甘特图 —— 35 人时
- project-task 视图增加甘特模式开关：eCharts 自定义系列渲染 task 的 estStarted/deadline + 依赖（如有），只做只读展示，不做拖拽改期。
- 数据源走既有 `task->ajaxGetProjectTasks($projectID, $taskID)`（`module/task/control.php`，另有 `ajaxGetUserTasks` / `ajaxGetDetail`），不新建后端通道。

---

## 5. P3 功能详案

### F10 OpenAPI 文档输出 —— 30 人时
- 扫描 `module/mcp/control.php` 的方法签名（现有 50+ 个，如 `listTasks`、`createBug`、`createRelease`）与 `module/api` 路由，生成 OpenAPI 3.0 JSON，挂在 `/api.php/docs`。
- 描述文本从 lang 文件取，缺失时退化显示方法名——**不阻塞生成**。

### F11 站会/会议纪要 —— 25 人时
- todo 增加 `type=standup`：自动拉取昨日完成/今日计划的 effort 数据预填。
- 保存时可一键归档为 doc 文档，进入项目文档库。
- 注意 `zt_todo.type` 为 `char(10)`、现有取值 `custom / cycle / bug / task / story`（`module/todo/lang/zh-cn.php::$lang->todo->typeList`）；新增 `standup` 除注册 typeList 外，还需同步适配各处按 type 分派的视图与 JS 分支。

### F12 README 徽章服务 —— 15 人时
- `/badge.php?type=bugs&product=1` 返回 SVG（shields.io 风格，本地渲染无外链）；同走 `api.php` 匿名路由。
- 类型：openBugs / latestRelease / openStory。匿名只读，带 product 维度开关。

---

## 6. 全局硬约束（所有功能共用）

1. **核心冻结**：不改 9.8.3 业务逻辑；定制优先落 `extension/` 或新模块。
2. **升级约定**：一切 DB 变更做成 `module/upgrade/model.php` 上的幂等方法（探 information_schema + IF NOT EXISTS），从 `execute()` 顶部调用，**不进** `switch($fromVersion)`；`db/zentao.sql` 同步更新；重跑必须零报错。
3. **UI 三层同改**：HTML 骨架 / JS（all.js + compat.js）/ CSS（10.1 皮肤 + compat.css）必须成套；`.outer` 包裹层不动；页面切换器用 `#pageNav` + ZUI dropdown/search-list 体系，禁止复活 9.8.3 的 showSearchMenu 路线。
4. **22.6 移植纪律延伸**：新模块代码不得引入 `zin` / `bi` / `dataview` / `$config->edition` 等上游新版概念。
5. **PHP 8.5**：禁止花括号偏移；改完清 `tmp/model/*.php`。
6. **安全**：所有匿名入口（repohook / roadmap / feedback / badge）必须有签名校验或频率限制或字段白名单三者之一以上；敏感凭据只在 `config/my.php` 或加密字段，不进 git。
7. **发布纪律**：每个功能落地时同步 `doc/release-notes/<version>.md`（增量，不是全量史）。

## 7. 里程碑建议

| 里程碑 | 内容 | 累计人时 |
|---|---|---|
| M1（9.11.0） | F3 通知渠道 + F1 Webhook 接入 + F12 徽章 | 105 |
| M2（9.12.0） | F2 CI/CD + F5 贡献者报表 | 210 |
| M3（9.13.0） | F4 评审 + F6 Roadmap/Changelog | 320 |
| M4（9.14.0） | F8 反馈门户 + F7 Wiki + F9 甘特 | 465 |
| M5（视需求） | F10 OpenAPI + F11 站会 | 520 |

> 累计人时按逐项求和（F1 60 + F2 80 + F3 30 + F4 70 + F5 25 + F6 40 + F7 50 + F8 60 + F9 35 + F10 30 + F11 25 + F12 15 = **520**）；原表 M2–M5 的累计值漏计了 F5 的 25 人时，此处已修正。

排序逻辑：F1 是 F2/F4 的地基（Webhook 接收端复用），F3 投入产出比最高先落地，F12 顺手带上凑一个"社区可见度"主题的 M1。

## 8. 风险登记

| 风险 | 等级 | 缓解 |
|---|---|---|
| 匿名入口引入攻击面 | 🔴 | 签名校验 + 频率限制 + 字段白名单，全部入口过一遍安全清单再合入 |
| CI 平台 API 变更 | 🟡 | engine 适配层隔离，单平台失效不影响整体 |
| pivot 报表 SQL 在老旧 MySQL 上退化 | 🟡 | 报表 SQL 禁用窗口函数，兼容 MySQL 5.6 |
| 与 22.6 移植线后续批次冲突 | 🟡 | 新功能一律新模块或 extension/，不碰移植线已声明冻结的文件 |

---

## 9. 实施建议（复核）

> 本节对 §0 优先级总表与 §7 里程碑建议做**复核**，按"**地基是否就绪 / DB 变更是否为零或极小 / 用户能否立刻感知 / 维护成本是否长期**"四项标准重新排序。**§7 的里程碑安排不建议照搬，以本节为准。**

### 9.1 结论

12 项功能中：**3 项立即实施、2 项裁剪后实施、7 项不做或推迟**。核心原则是"**复用已就绪地基**"优先于"**新建完整工作流**"。

### 9.2 分级

| 建议 | 编号 | 功能 | 判断依据 |
|---|---|---|---|
| ✅ **立即实施** | F3 | 通知渠道扩展（企微/飞书/Discord） | 唯一"用户立刻感知"的功能（群内出卡片）；webhook 骨架 + 钉钉/倍洽实现可直接参照；纯增量、零新表 |
| ✅ **立即实施** | F5 | 贡献者统计与社区健康报表 | pivot builtins 机制就绪 + eCharts 5.5.1 已入库（M0）；纯配置层增量、零新模块 |
| ✅ **立即实施** | F12 | README 徽章服务 | 15h 一张 SVG，与 F3 同属"社区可见度"主题 |
| ⚠️ **裁剪后实施** | F1 | 代码平台 Webhook 接入 | 是 F2/F4 的地基，但**首版只做 push 事件**（复用 `parseComment` / `saveAction2PMS` 写 action）；MR/issue 事件与状态流转砍到二期。60h → **~25h** |
| ⚠️ **裁剪后实施** | F6 | 公共 Roadmap 与 Changelog 生成 | **只做 Changelog 生成器**（本仓库手工维护 `doc/release-notes/`，生成器直接可用）；Roadmap 匿名页砍掉。40h → **~15h** |
| ❌ **推迟** | F2 | CI/CD 集成 | 最重投入（80h）；平台 API 差异大、**做了需长期维护**；ROI 最低 |
| ❌ **推迟** | F7 | Wiki 知识库 | doc 查询改造面广（8+ 处 `ne('template')` 需同步，见 §4 F7）+ 匿名访问安全面 |
| ❌ **推迟** | F9 | 甘特图 | eCharts 就绪，但只读甘特的价值取决于是否真用排期；可作 F5 之后的备选 |
| ❌ **不做** | F4 | 代码评审精简版 | 与 GitHub/Gitea 的 MR 评审重复，把意见搬进禅道价值有限 |
| ❌ **不做** | F8 | 社区反馈门户 | 无现成模块，全新建 + 反垃圾 + 需真实外部用户；需求未验证 |
| ❌ **不做** | F10 | OpenAPI 文档输出 | mcp 已服务 AI 场景，第三方集成需求未验证 |
| ❌ **不做** | F11 | 站会/会议纪要 | 场景窄 |

### 9.3 判断依据（结构性）

1. **排序方向应倒置。** §0 把最重的 F1+F2（140h）列为 P0 前两位，但"地基就绪、零风险、立即见效"的是 F3+F5+F12（约 70h）。应先用后者跑通新功能线的交付节奏（分支 → 实施 → 发版 → `release-notes`），再评估 F1。

2. **契合度。** 本仓库定位为"**禅道的稳定社区构建**"，而非"开源社区运营平台"。F1/F2/F4/F6/F8 均假设用户需要**完整的开源协作流**，但多数用户只需稳定使用禅道。因此"复用已就绪地基"（F3/F5/F12/F9）优先于"新建完整工作流"（F2/F4/F8）。

3. **时机正好，但必须单线。** 22.6 移植线 M0–M4 已基本交付（仅剩文档模板 CRUD），工时已释放，是做新功能的窗口；但须**单线推进**，避免 12 项并行。

4. **F1 的技术前提须重估。** 见 §2 F1 修正：现有 `parseComment` 只提取 ID、`saveAction2PMS` 只写 action，**均不流转状态**；"push 即流转"是新增能力。若坚持做状态流转，工时须重新估算。

### 9.4 建议的 M1'（第一批）

| 顺序 | 内容 | 人时 | 验收要点 |
|---|---|---|---|
| 1 | F3 通知渠道（企微/飞书/Discord） | 30 | 三渠道各发一条 bug 指派通知，群内卡片可点回系统 bug 页 |
| 2 | F5 贡献者报表 | 25 | pivot preview/show 可见；重跑 upgrade 不覆盖用户手改的报表 |
| 3 | F12 README 徽章 | 15 | `/badge.php` 返回 SVG；匿名只读带开关 |
| **合计** | | **70** | F3 因钉钉/倍洽已有可压缩，合计最低可至 ~65h |

**执行约定**：单分支 `feat/community-m1`，三项各一个 commit；逐项过 §6 硬约束与安全清单；发版前补 `doc/release-notes/<版本>.md`（只写本版增量）。

### 9.5 对 §7 的调整

- §7 的 M1（F3 + F1 + F12，105h）**拆解**：F3 + F12 提前到 M1'，F1 延后并裁剪（只做 push 事件）。
- §7 的 M2–M5 全部**暂缓**，待 M1' 交付复盘后再评估（届时 F1 裁剪版 + F6 裁剪版可组成 M2'）。
- 累计人时以 §7 修正后的 **520** 为准（原表 M2–M5 漏计 F5 的 25h）。

### 9.6 实施状态（2026-10-18）

**M1' 三项已实施、本地 dev 实测通过，随 9.10.3 发布**：

| 功能 | 状态 | 实现要点 |
|---|---|---|
| F3 通知渠道 | ✅ 已实施 | 渠道最终定为「**企业微信 / 飞书 / 默认**」——**钉钉与倍洽均已移除**（倍洽属追加要求："目前已经弃用"）。企业微信走 `msgtype=markdown`；飞书走 `interactive` 卡片 + `lark_md`（纯文本会丢链接，故用卡片）。连带清理仅为倍洽服务的收件人查询与 `needAssignTypes` 配置；`module/misc` 历史更新日志文案改为中性。 |
| F5 贡献者报表 | ✅ 已实施 | `module/pivot/config.php` 追加 `contributorrank`（贡献者排行）与 `communityhealth`（社区健康），分组「社区」；访问透视表页由 `syncBuiltins()` 幂等写入。 |
| F12 README 徽章 | ✅ 已实施 | 新增 `module/badge/` + `www/badge.php`（免登录 SVG，支持 `openbugs` / `openstory` / `latestrelease` + `product` 过滤，可经 `$config->badge->enabled` 关闭）。 |

**与计划的偏差**：① 渠道范围由「企微 / 飞书 / Discord」改为「企微 / 飞书」（Discord 未做），且钉钉、倍洽改为**删除**；② F12 未走 `api.php`（该入口需 entry 密钥，不适合公开徽章），改为独立免登录入口。

**遗留**：库中 `dingding` / `bearychat` 类型的历史 webhook 记录**不自动迁移**——编辑页将其显示为「默认」（不静默改写成其他渠道），保存后归为默认类型，需人工重配或删除。
