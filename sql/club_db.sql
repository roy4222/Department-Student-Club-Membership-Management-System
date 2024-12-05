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

-- 插入預設資料
INSERT INTO positions (name, description) VALUES 
('社長', '社團最高負責人'),
('副社長', '協助社長處理社團事務'),
('總務', '負責財務相關事務'),
('文書', '負責文書處理與記錄'),
('活動', '負責活動策劃與執行'),
('美宣', '負責美術宣傳設計');

-- 插入管理員帳號 (密碼: password123)
INSERT INTO members (student_id, name, department, class, email, phone, entry_date, role, password) VALUES 
('admin', '系統管理員', '資訊工程學系', 'A', 'admin@example.com', '0912345678', '2024-01-01', 'admin', '482c811da5d5b4bc6d497ffa98491e38');

-- 插入預設會員資料 (密碼: password123)
INSERT INTO members (student_id, name, department, class, email, phone, entry_date, role, password) VALUES 
('D1234567', '王小明', '資訊工程學系', 'A', 'wang@example.com', '0923456789', '2024-02-01', 'member', '482c811da5d5b4bc6d497ffa98491e38'),
('D1234568', '李小華', '資訊工程學系', 'A', 'lee@example.com', '0934567890', '2024-02-01', 'member', '482c811da5d5b4bc6d497ffa98491e38'),
('D1234569', '張小美', '資訊工程學系', 'B', 'chang@example.com', '0945678901', '2024-02-01', 'member', '482c811da5d5b4bc6d497ffa98491e38'),
('D1234570', '陳大文', '電機工程學系', 'A', 'chen@example.com', '0956789012', '2023-09-01', 'member', '482c811da5d5b4bc6d497ffa98491e38'),
('D1234571', '林小玲', '企業管理學系', 'B', 'lin@example.com', '0967890123', '2023-09-01', 'member', '482c811da5d5b4bc6d497ffa98491e38'),
('D1234572', '黃志明', '資訊管理學系', 'A', 'huang@example.com', '0978901234', '2023-09-01', 'member', '482c811da5d5b4bc6d497ffa98491e38'),
('D1234573', '吳雅琪', '數位媒體設計系', 'B', 'wu@example.com', '0989012345', '2023-09-01', 'member', '482c811da5d5b4bc6d497ffa98491e38'),
('D1234574', '謝佳玲', '資訊工程學系', 'A', 'hsieh@example.com', '0990123456', '2023-09-01', 'member', '482c811da5d5b4bc6d497ffa98491e38'),
('D1234575', '楊建國', '電機工程學系', 'B', 'yang@example.com', '0901234567', '2024-02-01', 'member', '482c811da5d5b4bc6d497ffa98491e38'),
('D1234576', '周小萍', '企業管理學系', 'A', 'chou@example.com', '0912345678', '2024-02-01', 'member', '482c811da5d5b4bc6d497ffa98491e38'),
('D1234577', '羅傑', '體育學系', 'A', 'roger@example.com', '0923456787', '2023-09-01', 'member', '482c811da5d5b4bc6d497ffa98491e38'),
('D1234578', '館長', '運動管理學系', 'A', 'gym@example.com', '0934567891', '2023-09-01', 'member', '482c811da5d5b4bc6d497ffa98491e38'),
('D1234579', '統神', '企業管理學系', 'B', 'toyz@example.com', '0945678902', '2024-02-01', 'member', '482c811da5d5b4bc6d497ffa98491e38'),
('D1234580', '超負荷', '電機工程學系', 'A', 'overload@example.com', '0956789013', '2024-02-01', 'member', '482c811da5d5b4bc6d497ffa98491e38');

-- 設定會員職位
INSERT INTO member_positions (member_id, position_id) VALUES 
(1, 1),  -- 管理員為社長
(2, 2),  -- 王小明為副社長
(3, 3),  -- 李小華為總務
(4, 4),  -- 陳大文為文書
(5, 5),  -- 林小玲為活動
(6, 6),  -- 黃志明為美宣
(7, 2),  -- 吳雅琪為副社長 (過去職位)
(7, 5),  -- 吳雅琪現為活動
(8, 6),  -- 謝佳玲為美宣
(9, 4),  -- 楊建國為文書
(10, 3), -- 周小萍為總務
(11, 5), -- 羅傑為活動組
(12, 1), -- 館長為社長 (過去職位)
(12, 2), -- 館長現為副社長
(13, 6), -- 統神為美宣
(14, 4); -- 超負荷為文書

-- 插入更多活動
INSERT INTO activities (name, description, location, event_date, registration_deadline, max_participants) VALUES 
('迎新茶會', '歡迎新成員加入！', '學生活動中心202室', '2024-03-01', '2024-02-25', 50),
('期中聚會', '讓我們一起交流討論', '圖書館研討室', '2024-04-15', '2024-04-10', 30),
('期末成果展', '展示本學期的學習成果', '演講廳', '2024-06-30', '2024-06-25', 100),
('程式設計工作坊', '基礎程式設計教學', '電腦教室', '2024-03-15', '2024-03-10', 40),
('創意企劃競賽', '團隊合作企劃比賽', '會議室', '2024-04-01', '2024-03-25', 60),
('社團幹部訓練', '幹部領導力培訓', '研討室', '2024-05-01', '2024-04-25', 20),
('電子專題講座', '邀請業界專家分享', '國際會議廳', '2024-05-15', '2024-05-10', 80),
('社團聯誼活動', '與其他社團交流', '活動中心', '2024-06-01', '2024-05-25', 100);

-- 插入活動參與記錄
INSERT INTO activity_participants (activity_id, member_id, attendance_status) VALUES 
(1, 2, 'attended'),
(1, 3, 'attended'),
(1, 4, 'attended'),
(1, 5, 'attended'),
(2, 2, 'registered'),
(2, 3, 'registered'),
(2, 6, 'registered'),
(2, 7, 'registered'),
(3, 4, 'registered'),
(3, 5, 'registered'),
(3, 8, 'registered'),
(4, 2, 'attended'),
(4, 6, 'attended'),
(4, 9, 'attended'),
(5, 3, 'attended'),
(5, 7, 'attended'),
(5, 10, 'attended'),
(6, 4, 'registered'),
(6, 8, 'registered'),
(7, 5, 'registered'),
(7, 9, 'registered'),
(8, 6, 'registered'),
(8, 10, 'registered'),
(1, 11, 'attended'),   -- 羅傑參加迎新茶會
(1, 12, 'attended'),   -- 館長參加迎新茶會
(2, 13, 'registered'), -- 統神報名期中聚會
(2, 14, 'registered'), -- 超負荷報名期中聚會
(3, 11, 'registered'), -- 羅傑報名期末成果展
(3, 12, 'registered'), -- 館長報名期末成果展
(4, 13, 'attended'),   -- 統神參加程式設計工作坊
(4, 14, 'attended'),   -- 超負荷參加程式設計工作坊
(5, 11, 'attended'),   -- 羅傑參加創意企劃競賽
(5, 12, 'attended'),   -- 館長參加創意企劃競賽
(6, 13, 'registered'), -- 統神報名社團幹部訓練
(6, 14, 'registered'); -- 超負荷報名社團幹部訓練

-- 插入會費繳納記錄
INSERT INTO fee_payments (member_id, semester, amount, payment_date, payment_type, receipt_number, status) VALUES 
(2, '112-2', 500.00, '2024-02-15', 'cash', 'R20240215001', 'paid'),
(3, '112-2', 500.00, '2024-02-16', 'transfer', 'R20240216001', 'paid'),
(4, '112-1', 500.00, '2023-09-15', 'cash', 'R20230915001', 'paid'),
(5, '112-1', 500.00, '2023-09-16', 'transfer', 'R20230916001', 'paid'),
(6, '112-1', 500.00, '2023-09-17', 'cash', 'R20230917001', 'paid'),
(7, '112-1', 500.00, '2023-09-18', 'transfer', 'R20230918001', 'paid'),
(8, '112-1', 500.00, '2023-09-19', 'cash', 'R20230919001', 'paid'),
(9, '112-2', 500.00, '2024-02-17', 'transfer', 'R20240217001', 'paid'),
(10, '112-2', 500.00, '2024-02-18', 'cash', 'R20240218001', 'paid'),
(11, '112-1', 500.00, '2023-09-20', 'cash', 'R20230920001', 'paid'),    -- 羅傑
(12, '112-1', 500.00, '2023-09-21', 'transfer', 'R20230921001', 'paid'), -- 館長
(13, '112-2', 500.00, '2024-02-19', 'cash', 'R20240219001', 'paid'),    -- 統神
(14, '112-2', 500.00, '2024-02-20', 'transfer', 'R20240220001', 'paid'); -- 超負荷
