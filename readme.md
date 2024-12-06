社團會費管理系統使用指南

這是什麼系統？
想像你是一個社團的財務負責人,每學期要管理 50 個成員的會費繳納情況。你需要:
- 記錄誰繳費了、誰還沒繳
- 提醒成員繳費期限
- 製作財務報表
- 追蹤歷年紀錄

這個系統就是要幫你自動化處理這些工作！就像是一個數位化的帳本加上貼心的提醒小幫手。

使用這個系統的好處

🎯 省時省力
- 不用再用 Excel 手動記帳
- 自動發送繳費提醒
- 一鍵產生各種報表

🔍 資料更準確
- 避免人工輸入錯誤
- 歷史紀錄永久保存
- 資料統計更精確

💡 管理更有效
- 即時掌握繳費狀況
- 自動追蹤逾期帳款
- 完整的權限管理

系統是如何運作的？
讓我們用餐廳來比喻這個系統：

1. 前台服務生(使用者介面)
- 接收使用者的要求
- 顯示資料和結果
- 提供友善的操作方式

2. 廚房(後台程式)
- 處理所有業務邏輯
- 確保資料正確性
- 執行各種運算

3. 倉庫(資料庫)
- 儲存所有資料
- 管理資料關聯
- 確保資料安全

4. 外送員(通知系統)
- 發送繳費提醒
- 通知逾期資訊
- 寄送重要通知

資料是如何儲存的？
就像每個家庭都有自己的相簿、記事本和收據,我們的系統也會把不同類型的資料分開儲存：

👥 會員資料表 (members)
就像是每個成員的身分證,記錄基本資料：
- 學號 (student_id)
- 姓名 (name)
- 系所 (department)
- 班級 (class)
- 電子郵件 (email)
- 電話 (phone)
- 入學日期 (entry_date)
- 角色 (role): 管理員或一般成員
- 建立與更新時間 (created_at, updated_at)

👔 職位相關資料 
分成兩個表格來記錄,就像是社團的組織圖：

1. 職位資料表 (positions)：
- 職位名稱 (name)
- 職位說明 (description)
- 建立時間 (created_at)

2. 會員職位關聯表 (member_positions)：
記錄誰在什麼時候擔任什麼職位

📅 活動相關資料
分成兩個表格來記錄,就像是活動手冊：

1. 活動資料表 (activities)：
- 活動名稱 (name)
- 活動說明 (description)
- 活動地點 (location)
- 活動日期 (event_date)
- 報名截止日 (registration_deadline)
- 人數上限 (max_participants)

2. 活動參與記錄表 (activity_participants)：
記錄誰參加了什麼活動,出席狀況如何

🧾 會費繳納記錄表 (fee_payments)
就像收據一樣,記錄每筆繳費：
- 繳費學期 (semester)
- 繳費金額 (amount)
- 繳費日期 (payment_date)
- 繳費方式 (payment_type): 現金或轉帳
- 收據編號 (receipt_number)
- 備註 (notes)
- 繳費狀態 (status): 已繳、待繳、取消

主要功能介紹

1. 會員管理
- 基本資料管理（姓名、學號、聯繫方式、入學時間）
- 職位紀錄
- 權限分級：社長、幹部、一般成員

2. 會費管理
- 每學期會費金額設定
- 繳費狀態追蹤
- 繳費紀錄管理

3. 通知系統
- 繳費期限前提醒
- 逾期提醒
- 通過Email發送通知

4. 報表功能
- Excel格式匯出
- 包含欄位：姓名、學號、繳費日期、金額
- 依照繳費狀態顯示會員清單

如何開始使用？

系統需求
- PHP >= 7.4
- MySQL >= 5.7
- Web服務器（Apache/Nginx）
- Composer（PHP套件管理器）

安裝步驟
就像煮菜一樣,讓我們一步一步來：

1. 下載系統
```bash
git clone [repository_url]
cd club-fee-system
```

2. 安裝必要的工具
```bash
composer install
```

3. 設定系統
```bash
cp .env.example .env
# 編輯 .env 檔案,設定資料庫連線等資訊
```

4. 初始化資料庫
```bash
php scripts/init_database.php
```

常見問題解答

❓ 問題: 忘記密碼怎麼辦？
✅ 解答: 請聯繫系統管理員重設密碼。

❓ 問題: 如何修改會費金額？
✅ 解答: 請在「會費設定」頁面進行修改。

❓ 問題: 可以匯入舊的 Excel 資料嗎？
✅ 解答: 可以,系統提供資料匯入功能。

給開發者的資訊

技術架構
- 後端：PHP
- 資料庫：MySQL
- 前端：HTML, CSS, JavaScript

專案結構
```
WEEK10/
├── config/               # 配置文件目錄
│   └── database.php     # 資料庫配置
│
├── includes/            # 共用組件目錄
│   ├── header.php      # 頁面頭部
│   └── footer.php      # 頁面底部
│
├── public/             # 公開訪問目錄
│   ├── activities/    # 活動管理
│   ├── api/          # API 接口
│   ├── auth/         # 認證相關
│   ├── css/          # CSS 樣式
│   ├── exports/      # 匯出功能
│   ├── members/      # 會員管理
│   ├── payments/     # 付款管理
│   ├── dashboard.php # 儀表板
│   ├── fees.php      # 會費管理
│   └── index.php     # 入口文件
│
├── sql/              # SQL 腳本目錄
│   └── club_db.sql  # 資料庫結構
│
├── vendor/          # Composer 依賴目錄
├── .gitignore      # Git 忽略文件
├── composer.json   # Composer 配置
├── composer.lock   # Composer 鎖定文件
└── readme.md      # 專案說明文件


資料庫結構
```sql
-- 建立資料庫
CREATE DATABASE IF NOT EXISTS club_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE club_db;

-- 會員資料表
CREATE TABLE members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id VARCHAR(10) UNIQUE NOT NULL,
    name VARCHAR(50) NOT NULL,
    department VARCHAR(50) NOT NULL,
    class VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    entry_date DATE NOT NULL,
    role ENUM('admin', 'member') DEFAULT 'member',
    password VARCHAR(32) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_student_id (student_id),
    INDEX idx_department_class (department, class)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 職位資料表
CREATE TABLE positions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 會員職位關聯表
CREATE TABLE member_positions (
    member_id INT NOT NULL,
    position_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (member_id, position_id),
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 活動資料表
CREATE TABLE activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    location VARCHAR(100),
    event_date DATE NOT NULL,
    registration_deadline DATE,
    max_participants INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_event_date (event_date),
    INDEX idx_registration_deadline (registration_deadline)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 活動參與記錄表
CREATE TABLE activity_participants (
    activity_id INT NOT NULL,
    member_id INT NOT NULL,
    attendance_status ENUM('registered', 'attended', 'absent') DEFAULT 'registered',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (activity_id, member_id),
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    INDEX idx_attendance_status (attendance_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 會費繳納記錄表
CREATE TABLE fee_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    semester VARCHAR(10) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_type ENUM('cash', 'transfer') NOT NULL,
    receipt_number VARCHAR(50),
    notes TEXT,
    status ENUM('paid', 'pending', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    INDEX idx_semester (semester),
    INDEX idx_payment_date (payment_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

測試方式
```bash
# 運行所有測試
./vendor/bin/phpunit tests/

# 運行特定測試
./vendor/bin/phpunit tests/MemberTest.php
```

---
🔔 需要協助？
- 技術支援：support@email.com
- 使用手冊：[docs/user-guide.pdf]
- 問題回報：[GitHub Issues]