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
('會長', '系學會最高負責人，對外代表系學會，對內統籌各部門運作'),
('副會長', '協助會長處理系務，以及系學會各項行政事務的規劃與執行'),
('秘書長', '負責會議記錄、文件管理、行政協調等事務'),
('財務長', '負責系學會預算編列、經費核銷、帳務管理等工作'),
('活動部長', '策劃與執行系上各項活動，促進系上感情交流'),
('學術部長', '規劃讀書會、講座等學術活動，促進系上學術發展'),
('權益部長', '維護系上學生權益，處理系上學生反映意見'),
('公關部長', '負責對外聯繫、宣傳、尋求合作機會等工作');

-- 插入管理員帳號 (密碼: 2486)
INSERT INTO members (student_id, name, department, class, email, phone, entry_date, role, password) VALUES 
('ROGER', '羅傑', '資訊工程學系', 'A', 'roger788@gmail.com', '0912345678', '2024-01-01', 'admin', 'bb03e43ffe34eeb242a2ee4a4f125e56');

-- 插入預設會員資料 (密碼需使用明文存入資料庫)
INSERT INTO members (student_id, name, department, class, email, phone, entry_date, role, password) VALUES 
('D01', '溫水', '資訊工程學系', 'A', 'zhang@example.com', '0912345678', '2023-09-01', 'member', 'bb03e43ffe34eeb242a2ee4a4f125e56'),
('D02', '佳樹', '資訊工程學系', 'B', 'li@example.com', '0923456789', '2023-09-01', 'member', 'bb03e43ffe34eeb242a2ee4a4f125e56'),
('D03', '八奈見', '資訊管理學系', 'A', 'wang@example.com', '0934567890', '2023-09-01', 'member', 'bb03e43ffe34eeb242a2ee4a4f125e56'),
('D04', '檸檬', '電機工程學系', 'A', 'chen@example.com', '0956789012', '2023-09-01', 'member', 'bb03e43ffe34eeb242a2ee4a4f125e56'),
('D05', '林小玲', '企業管理學系', 'B', 'lin@example.com', '0967890123', '2023-09-01', 'member', 'bb03e43ffe34eeb242a2ee4a4f125e56'),
('D06', '熊問安', '體育學系', 'A', 'roger@example.com', '0923456787', '2023-09-01', 'member', 'bb03e43ffe34eeb242a2ee4a4f125e56'),
('D07', '館長', '運動管理學系', 'A', 'gym@example.com', '0934567891', '2023-09-01', 'member', 'bb03e43ffe34eeb242a2ee4a4f125e56'),
('D08', '統神', '企業管理學系', 'B', 'toyz@example.com', '0945678902', '2024-02-01', 'member', 'bb03e43ffe34eeb242a2ee4a4f125e56'),
('D09', '超負荷', '電機工程學系', 'A', 'overload@example.com', '0956789013', '2024-02-01', 'member', 'bb03e43ffe34eeb242a2ee4a4f125e56');

-- 設定會員職位 
INSERT INTO member_positions (member_id, position_id) VALUES 
(1, 1),  -- 羅傑為會長
(2, 2),  -- 溫水為副會長
(3, 3),  -- 佳樹為秘書長
(4, 4),  -- 八奈見為財務長
(5, 5),  -- 檸檬為活動部長
(6, 6),  -- 林小玲為學術部長
(7, 7),  -- 熊問安為權益部長
(8, 8),  -- 館長為公關部長
(9, 6);  -- 統神為學術部長

-- 插入更多活動
INSERT INTO activities (name, description, location, event_date, registration_deadline, max_participants) VALUES 
('系學會迎新大會', '歡迎大一新生加入本系大家庭！有精彩表演和系上教授致詞', '國際會議廳', '2024-01-01', '2023-12-25', 200),
('期中系員大會', '討論系務及系學會重要事項', '演講廳', '2024-04-15', '2024-04-10', 150),
('系學會成果發表會', '展示本學期系學會各部門成果', '學生活動中心大禮堂', '2024-06-30', '2024-06-25', 200),
('考前系讀書會', '舉辦期中考前讀書會，學長姐分享讀書技巧', '圖書館研討室', '2024-03-15', '2024-03-10', 80),
('系際盃運動大賽', '與其他系所進行籃球、排球等比賽', '體育館', '2024-04-01', '2024-03-25', 100),
('系學會幹部訓練營', '新任幹部職務交接與培訓', '研討室', '2024-05-01', '2024-04-25', 50),
('系上聯合晚會', '舉辦系上年度晚會，促進系上感情交流', '活動中心大禮堂', '2024-06-01', '2024-05-25', 200),
('系學會耶誕晚會', '一年一度的耶誕晚會，有美食、表演和交換禮物活動', '學生活動中心大禮堂', '2023-12-23', '2023-12-15', 200),
('系學會年度總結會議', '回顧本年度系學會成果，討論未來規劃', '演講廳', '2023-12-28', '2023-12-20', 150),
('跨年聯誼晚會', '與別系所舉辦跨年活動，一起迎接新的一年', '活動中心大禮堂', '2023-12-31', '2023-12-25', 250);

-- 插入活動參與記錄
INSERT INTO activity_participants (activity_id, member_id, attendance_status) VALUES 
(1, 1, 'attended'),  -- 羅傑參加系學會迎新大會
(1, 2, 'attended'),  -- 溫水參加系學會迎新大會
(1, 3, 'attended'),  -- 佳樹參加系學會迎新大會
(1, 4, 'attended'),  -- 八奈見參加系學會迎新大會
(2, 1, 'attended'),  -- 羅傑參加期中系員大會
(2, 2, 'attended'),  -- 溫水參加期中系員大會
(2, 5, 'attended'),  -- 檸檬參加期中系員大會
(2, 6, 'attended'),  -- 林小玲參加期中系員大會
(3, 3, 'attended'),  -- 佳樹參加系學會成果發表會
(3, 4, 'attended'),  -- 八奈見參加系學會成果發表會
(3, 7, 'attended'),  -- 熊問安參加系學會成果發表會
(4, 1, 'attended'),  -- 羅傑參加考前系讀書會
(4, 5, 'attended'),  -- 檸檬參加考前系讀書會
(4, 8, 'attended'),  -- 館長參加考前系讀書會
(5, 2, 'attended'),  -- 溫水參加系際盃運動大賽
(5, 6, 'attended'),  -- 林小玲參加系際盃運動大賽
(5, 9, 'attended'),  -- 統神參加系際盃運動大賽
(6, 3, 'attended'),  -- 佳樹參加系學會幹部訓練營
(6, 7, 'attended'),  -- 熊問安參加系學會幹部訓練營
(7, 4, 'attended'),  -- 八奈見參加系上聯合晚會
(7, 8, 'attended'),  -- 館長參加系上聯合晚會
(8, 1, 'attended'),  -- 羅傑參加系學會耶誕晚會
(8, 2, 'attended'),  -- 溫水參加系學會耶誕晚會
(8, 3, 'attended'),  -- 佳樹參加系學會耶誕晚會
(8, 4, 'attended'),  -- 八奈見參加系學會耶誕晚會
(8, 5, 'attended'),  -- 檸檬參加系學會耶誕晚會
(8, 6, 'attended'),  -- 林小玲參加系學會耶誕晚會
(9, 1, 'attended'),  -- 羅傑參加系學會年度總結會議
(9, 2, 'attended'),  -- 溫水參加系學會年度總結會議
(9, 3, 'attended'),  -- 佳樹參加系學會年度總結會議
(9, 4, 'attended'),  -- 八奈見參加系學會年度總結會議
(10, 1, 'attended'), -- 羅傑參加跨年聯誼晚會
(10, 2, 'attended'), -- 溫水參加跨年聯誼晚會
(10, 3, 'attended'), -- 佳樹參加跨年聯誼晚會
(10, 5, 'attended'), -- 檸檬參加跨年聯誼晚會
(10, 7, 'attended'), -- 熊問安參加跨年聯誼晚會
(10, 8, 'attended'), -- 館長參加跨年聯誼晚會
(10, 9, 'attended'); -- 統神參加跨年聯誼晚會


-- 插入會費繳納記錄
INSERT INTO fee_payments (member_id, semester, amount, payment_date, payment_type, notes, status) VALUES 
(2, '112-2', 500.00, '2024-02-15', 'cash', '', 'paid'),    -- 溫水
(3, '112-2', 500.00, '2024-02-16', 'transfer', '', 'paid'), -- 佳樹
(4, '112-2', 500.00, '2023-09-20', 'cash', '', 'paid'),    -- 八奈見
(5, '112-2', 500.00, '2023-09-21', 'transfer', '', 'paid'), -- 檸檬
(8, '112-2', 500.00, '2024-02-19', 'cash', '', 'paid'),    -- 館長
(9, '112-2', 500.00, '2024-02-20', 'transfer', '', 'paid'); -- 統神
