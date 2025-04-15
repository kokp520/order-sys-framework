# 時間規劃

回信：
hi, 我這邊預估4/24(四) 完成回信給你, 
本週剛好要值班所以時間上會比較吃緊，因為時間拉比較長，我這邊也另外列一下細節

hi, 本週剛好要值班時間上會比較吃緊
依照這個home quiz, 我大致上會用以下方式評估：
我預計假日才會有時間處理，避免假日也會有值班問題需要處理，我預估4/23完成回信給你

我先假定optional的部分就自由發揮 我都先以自己對於需求的想像設計

大致上我這邊會分成
環境架設, 效能分析, 了解nestjs 設計nestjs, 框架效能分析

本週會要值班 平日晚上可能沒時間處理
預計假日可能會有時間

因為email信件比較不方便來回確認關係，我先假定nice to have的部分就自由發揮

預計任務：
- laravel+nestjs環境架設: 1hr
- 效能分析：牽扯到php應用層資料庫，依照題目這邊會以woocommerce為主，其他則輔
大致上會可以測試 高流量行行為 api rqs, 系統資源, (front end, wordpress/woocommerce目前看起來應該是主要為頁面上導向)

- 依照效能需要分析項目做用例建立：
static page, db, session,  複合操作

- 壓力測試 ab, k6(之前讀書會有同仁分享, 剛好趁此機會用該工具)

apache bench: ab

k6: 

- 設計模式：


wrk: 模擬多用戶
siege: 模擬多用戶並發

2-1. WooCommerce 關聯 Table

PHP 運行慢	外掛過多 / 無快取	啟用 OPcache / 移除不用 plugin
DB 查詢慢	postmeta 查詢多、未 index	補索引、用自定欄位儲存、啟用 object cache
頁面回應慢	每次都重新產出頁面	使用快取機制，如 WP Super Cache、FastCGI cache
Session 寫入慢	WooCommerce 預設用 wp_options	改用 Redis 儲存 session


3-2. 框架比較
面向	Laravel	Nest.js
語言	PHP	TypeScript (Node.js)
架構風格	MVC 為主，支援 DI	完整的 DI + 模組化設計
ORM	Eloquent	TypeORM / Prisma 可選
社群生態	非常成熟，商業系統多	新興但強大，與 Angular 相近
測試支援	有，偏後期加入	測試為第一公民（Jest、Supertest）
效能	PHP 單執行緒，較依賴快取	Node.js 非同步，適合 I/O 密集


3-3. 效能測試比較（可用 wrk）
項目	Laravel	Nest.js
同時 100 請求	較容易到瓶頸（同步）	Node 表現穩定（非同步）
初始化時間	PHP 較快（啟動快）	Nest.js 冷啟稍慢
單請求延遲	幾十毫秒	幾毫秒（如快取好）

3-4. UML / 設計模式描述（選填）
框架	關鍵類別與設計
Laravel	Controller → Service → Model
Service 類可用 DI，Model 負責資料庫
Nest.js	Controller → Service → Repository
用 Module 抽象領域，用 Decorator 管理 metadata


---

🔧 先架好測試環境（local nginx + WordPress/Woo + Laravel/Nest）

📊 用 wrk / ab 壓測首頁與訂單 API

🗂️ 整理各架構的 table 與 schema，並標記設計問題

⚙️ 撰寫 CRUD 程式並測試效能

📘 撰成報告或整理成筆記（我可以幫你整理成 Markdown 或 PPT）