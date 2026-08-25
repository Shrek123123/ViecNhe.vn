<!-- <?php
// Kết nối đến MySQL để tạo cơ sở dữ liệu

include_once 'config.php'; // Kết nối đến cơ sở dữ liệu MySQL

// Tạo cơ sở dữ liệu jobhive nếu chưa tồn tại
$conn->query("CREATE DATABASE IF NOT EXISTS jobhive");

// Chọn cơ sở dữ liệu jobhive
$conn->select_db("jobhive");

// Tạo bảng user
$stmt = $conn->query("CREATE TABLE IF NOT EXISTS user (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL,
    user_type VARCHAR(30) NOT NULL,
    email VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verified BOOLEAN DEFAULT FALSE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");
if ($stmt === TRUE) {
    echo "Table user created successfully <br>";
} else {
    echo "Error creating table: " . $conn->error;
}

// Tạo bảng jobseeker_profile
$stmt = $conn->query("CREATE TABLE IF NOT EXISTS jobseeker_profile (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    jobseeker_id INT UNSIGNED NOT NULL,
    profile_description TEXT,
    profile_pic VARCHAR(255),
    phone_number VARCHAR(20) UNIQUE,
    gender ENUM('Male', 'Female', 'Other', 'Prefer not to say'),
    address VARCHAR(255),
    date_of_birth DATE,
    marital_status VARCHAR(50),
    cv_file VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (jobseeker_id) REFERENCES user(id) ON DELETE CASCADE
)");
if ($stmt === TRUE) {
    echo "Table jobseeker_profile created successfully <br>";
} else {
    echo "Error creating table: " . $conn->error;
}

// Tạo bảng employer_profile
$stmt = $conn->query("CREATE TABLE IF NOT EXISTS employer_profile (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employer_id INT UNSIGNED NOT NULL,
    profile_description TEXT, -- Có thể để NULL nếu không bắt buộc
    profile_pic VARCHAR(255),
    -- Các trường mới được thêm vào
    phone_number VARCHAR(20) UNIQUE, -- Số điện thoại, có thể để UNIQUE nếu bạn muốn mỗi số chỉ thuộc về 1 profile
    gender ENUM('Male', 'Female', 'Other', 'Prefer not to say'), -- Giới tính
    address VARCHAR(255), -- Địa chỉ hiện tại
    date_of_birth DATE, -- Ngày sinh (yyyy-mm-dd)
    marital_status VARCHAR(50), -- Tình trạng hôn nhân (ví dụ: 'Single', 'Married', 'Divorced')
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- Thêm trường updated_at để theo dõi sửa đổi
    FOREIGN KEY (employer_id) REFERENCES user(id) ON DELETE CASCADE
)");
if ($stmt === TRUE) {
    echo "Table employer_profile created successfully <br>";
} else {
    echo "Error creating table: " . $conn->error;
}

// Tạo bảng job
$stmt = $conn->query("CREATE TABLE IF NOT EXISTS job (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    posted_by_employer_id INT UNSIGNED NOT NULL,
    company_name VARCHAR(100) NOT NULL,
    company_size VARCHAR(50),
    job_title VARCHAR(100) NOT NULL,
    job_description TEXT NOT NULL,
    job_benefit TEXT,
    job_requirement TEXT,
    job_location VARCHAR(100) NOT NULL,
    no_employee_needed INT UNSIGNED NOT NULL,
    employee_gender VARCHAR(20) NULL,
    salary VARCHAR(30) NOT NULL,
    post_duration INT UNSIGNED NOT NULL,
    contact_email VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(20),
    job_type VARCHAR(50) NOT NULL,
    job_category VARCHAR(100) NOT NULL,
    required_certification VARCHAR(255),
    job_experience VARCHAR(100),
    company_logo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('pending_review', 'approved', 'rejected') DEFAULT 'pending_review',
    job_post_number_by_employer INT(11) NOT NULL DEFAULT 0,
    FOREIGN KEY (posted_by_employer_id) REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE
)");
if ($stmt === TRUE) {
    echo "Table job created successfully <br>";
} else {
    echo "Error creating table: " . $conn->error;
}

// Tạo bảng application
$stmt = $conn->query("CREATE TABLE IF NOT EXISTS application (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id INT UNSIGNED NOT NULL,
    jobseeker_id INT UNSIGNED NOT NULL,
    status ENUM('pending', 'viewed', 'interviewed', 'hired', 'rejected') DEFAULT 'pending',
    fullname VARCHAR(100) NOT NULL,
    phonenumber VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cv_file VARCHAR(255),
    allow_search BOOLEAN DEFAULT FALSE,
    hidden_from_employer BOOLEAN DEFAULT FALSE,
    UNIQUE (job_id, jobseeker_id),
    FOREIGN KEY (job_id) REFERENCES job(id) ON DELETE CASCADE,
    FOREIGN KEY (jobseeker_id) REFERENCES user(id) ON DELETE CASCADE
)");
if ($stmt === TRUE) {
    echo "Table application created successfully <br>";
} else {
    echo "Error creating table: " . $conn->error;
}

// Tạo bảng feedback
$stmt = $conn->query("CREATE TABLE IF NOT EXISTS feedback (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    user_type ENUM('jobseeker', 'recruiter', 'admin') NOT NULL,
    content TEXT NOT NULL,
    star_rating TINYINT UNSIGNED NOT NULL CHECK (star_rating BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
)");
if ($stmt === TRUE) {
    echo "Table feedback created successfully <br>";
} else {
    echo "Error creating table: " . $conn->error;
}

// Tạo bảng company
$stmt = $conn->query("CREATE TABLE IF NOT EXISTS company (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employer_id INT(6) UNSIGNED NOT NULL,
    company_name VARCHAR(100) NOT NULL,
    company_address VARCHAR(255) NOT NULL,
    industry VARCHAR(100) NOT NULL,
    information TEXT,
    banner_logo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES user(id)
)");
if ($stmt === TRUE) {
    echo "Table company created successfully <br>";
} else {
    echo "Error creating table: " . $conn->error;
}

$stmt = $conn->query("CREATE TABLE IF NOT EXISTS job_interest_count (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id INT(6) UNSIGNED NOT NULL,
    interest_count INT(6) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES job(id) ON DELETE CASCADE
)");
if ($stmt === TRUE) {
    echo "Table job_interest_count created successfully <br>";
} else {
    echo "Error creating table: " . $conn->error;
}
// Tạo bảng saved_jobs
$stmt = $conn->query("CREATE TABLE IF NOT EXISTS saved_jobs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    job_id INT UNSIGNED NOT NULL,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES job(id) ON DELETE CASCADE,
    UNIQUE (user_id, job_id)
)");
if ($stmt === TRUE) {
    echo "Table saved_jobs created successfully <br>";
} else {
    echo "Error creating table: " . $conn->error;
}

//Dữ liệu mẫu cho bảng user
// Sample data for user table
$username = 'Employer1';
$user_type = 'employer';
$email = 'employer1@gmail.com';
$password_plain = '123';
$password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);

$sql_user = "INSERT INTO user (username, user_type, email, password) VALUES (?, ?, ?, ?)";
$stmt_user = $conn->prepare($sql_user);
if ($stmt_user) {
    $stmt_user->bind_param("ssss", $username, $user_type, $email, $password_hashed);
    if ($stmt_user->execute()) {
        echo "Sample user data inserted successfully<br>";
    } else {
        echo "Error inserting sample user data: " . $stmt_user->error . "<br>";
    }
    $stmt_user->close();
} else {
    echo "Error preparing statement for user table: " . $conn->error . "<br>";
}

// // Dữ liệu mẫu cho bảng job
$logo_it = "https://tse2.mm.bing.net/th?id=OIP.47mNS8ylMCGxCLYh5eW08wHaF7&pid=Api&P=0&h=180";
$logo_marketing = "https://static.vecteezy.com/system/resources/previews/007/978/948/original/digital-marketing-logo-design-template-vector.jpg"; // Giữ nguyên
$logo_finance = "https://www.onlinelogomaker.com/blog/wp-content/uploads/2017/06/Handling-Finances.jpg"; // Giữ nguyên
$logo_healthcare = "https://tse3.mm.bing.net/th?id=OIP.-VH9JiaEYFtLCToj3rI8JgHaE7&pid=Api&P=0&h=180";
$logo_government = "https://tse3.mm.bing.net/th?id=OIP.RroPWyTmgQUEEsAN8VtjUAHaHZ&pid=Api&P=0&h=180";

// Mảng dữ liệu các việc làm cần chèn với các logo đã cập nhật
$jobs_data = [
    [1, 'Google', '1000+', 'Software Engineer', 'Develop scalable software systems.', 'Health insurance, stock options, gym membership.', '3+ years in software development.', 'Hanoi', 3, '$3000 - $4000', 30, 'hr@google.com', '0123456789', 'Full-time', 'IT & Software', 'BSc in Computer Science', '3 years', $logo_it, 'approved'],
    [1, 'Microsoft', '1000+', 'Backend Developer Intern', 'Assist backend development using .NET.', 'Training, mentorship, chance to go full-time.', 'Basic C#, SQL knowledge.', 'HCMC', 2, '$500', 60, 'jobs@microsoft.com', '0988123456', 'Internship', 'IT & Software', 'None', 'None', $logo_it, 'approved'],
    [1, 'MarketingPro', '100-200', 'Digital Marketer', 'Run ad campaigns and analyze KPIs.', 'Flexible schedule, training.', 'Experience with Facebook Ads.', 'Da Nang', 1, '$700 - $1000', 45, 'jobs@marketingpro.vn', '0909988776', 'Part-time', 'Marketing', 'None', '1 year', $logo_marketing, 'approved'],
    [1, 'HealthFirst', '500+', 'Nurse Assistant', 'Support patient care and assist doctors.', 'Night shift bonus, uniform.', 'Nursing certification.', 'Hue', 4, '$500 - $800', 30, 'recruit@healthfirst.vn', '0944123456', 'Full-time', 'Healthcare', 'Certified Nursing Assistant', '2 years', $logo_healthcare, 'approved'],
    [1, 'CityOffice', '200+', 'Government Clerk', 'Handle public documents and records.', 'Civil servant benefits.', 'Detail-oriented, good communication.', 'Hanoi', 2, '$400 - $600', 60, 'recruit@cityoffice.gov.vn', '0332123456', 'Full-time', 'Government & Public Sector', 'Bachelor in Public Admin', '1 year', $logo_government, 'approved'],
    [1, 'FinSecure', '300+', 'Finance Analyst', 'Evaluate financial risks and make forecasts.', 'Performance bonuses.', 'Excel, financial modeling.', 'HCMC', 1, '$1200 - $1800', 30, 'hr@finsecure.com', '0387123456', 'Full-time', 'Finance', 'CFA Level 1', '2 years', $logo_finance, 'approved'],
    [1, 'Google', '1000+', 'Data Scientist', 'Work on data pipelines and ML models.', 'Free meals, wellness budget.', 'Python, SQL, ML knowledge.', 'Remote', 2, '$4000 - $5000', 45, 'jobs@google.com', '0123456789', 'Remote', 'IT & Software', 'BSc in Data Science', '2 years', $logo_it, 'approved'],
    [1, 'GovConnect', '150+', 'HR Specialist', 'Handle recruitment and staff evaluation.', '13th-month salary, training.', 'Good at labor laws.', 'Hai Phong', 1, '$600 - $900', 30, 'contact@govconnect.gov.vn', '0367788990', 'Part-time', 'Government & Public Sector', 'HR Certification', '3 years', $logo_government, 'approved'],
    [1, 'CareHub', '50-100', 'Receptionist', 'Manage bookings and patient reception.', 'Afternoon off weekly.', 'Polite, fast learner.', 'Hanoi', 1, '$350 - $500', 30, 'contact@carehub.vn', '0922334455', 'Part-time', 'Healthcare', 'None', '6 months', $logo_healthcare, 'approved'],
    [1, 'AdVision', '50+', 'Marketing Intern', 'Assist in campaign planning and social media.', 'Certificate after internship.', 'Photoshop, Canva.', 'Remote', 2, '$200', 45, 'jobs@advision.vn', '0909099999', 'Internship', 'Marketing', 'None', 'None', $logo_marketing, 'approved'],
    [1, 'Designify', '20+', 'Web Designer', 'Design landing pages and brand websites.', 'Remote option.', 'Figma, HTML/CSS.', 'HCMC', 1, '$800 - $1200', 30, 'team@designify.vn', '0987654321', 'Freelance', 'IT & Software', 'None', '2 years', $logo_it, 'approved'],
    [1, 'MoneyTree', '500+', 'Accountant', 'Manage invoices and tax reports.', 'Clear career path.', 'Excel, accounting software.', 'Hanoi', 2, '$600 - $1000', 30, 'hr@moneytree.com', '0933445566', 'Full-time', 'Finance', 'CPA preferred', '3 years', $logo_finance, 'approved'],
    [1, 'HospTech', '200+', 'Healthcare Consultant', 'Optimize hospital workflows.', 'Travel allowance.', 'Good communication, analysis.', 'Remote', 1, '$1000 - $1500', 45, 'consult@hosptech.com', '0911999888', 'Remote', 'Healthcare', 'MBA/Healthcare', '5 years', $logo_healthcare, 'approved'],
    [1, 'BuildGov', '300+', 'Civil Engineer', 'Supervise road and bridge projects.', 'Lunch, housing support.', 'AutoCAD, site supervision.', 'Da Nang', 4, '$1000 - $2000', 60, 'hr@buildgov.gov.vn', '0966887788', 'Full-time', 'Government & Public Sector', 'Civil Eng Degree', '3 years', $logo_government, 'approved'],
    [1, 'UserLab', '80+', 'UX Researcher', 'Test product usability, conduct user interviews.', 'Remote work supported.', 'User research methods.', 'Remote', 1, '$1500 - $2000', 30, 'ux@userlab.com', '0912345678', 'Full-time', 'IT & Software', 'UX Certificate', '2 years', $logo_it, 'approved'],
    [1, 'TrendSpark', '50+', 'Social Media Manager', 'Plan and execute campaigns.', 'Monthly team dinners.', 'Tiktok/Facebook/Instagram experience.', 'HCMC', 1, '$900 - $1200', 30, 'team@trendspark.vn', '0977886655', 'Remote', 'Marketing', 'None', '2 years', $logo_marketing, 'approved'],
    [1, 'MedTech', '400+', 'Hospital IT Support', 'Manage hardware and hospital network.', 'On-call allowance.', 'Basic networking + support tools.', 'Can Tho', 2, '$600 - $900', 30, 'support@medtech.vn', '0908111222', 'Full-time', 'Healthcare', 'None', '2 years', $logo_healthcare, 'approved'],
    [1, 'GovInsights', '100+', 'Public Policy Analyst', 'Evaluate social and economic policy.', 'Pension, public sector training.', 'Research skills, statistics.', 'Hanoi', 1, '$1000 - $1300', 60, 'recruit@govinsights.vn', '0977554433', 'Full-time', 'Government & Public Sector', 'MPA/MPP preferred', '3 years', $logo_government, 'approved'],
    [1, 'CheckFunds', '40+', 'Financial Auditor', 'Audit firm financial statements.', 'Travel bonus.', 'Attention to detail.', 'Remote', 2, '$1200 - $1800', 30, 'auditor@checkfunds.com', '0933998877', 'Freelance', 'Finance', 'CPA required', '4 years', $logo_finance, 'approved'],
    [1, 'WebLift', '15+', 'SEO Specialist', 'Improve search rankings for clients.', 'Remote, flexible hours.', 'Keyword research, backlinks.', 'Remote', 1, '$600 - $1000', 30, 'seo@weblift.vn', '0944556677', 'Freelance', 'Marketing', 'None', '2 years', $logo_marketing, 'approved']
];

// Biến báo lỗi
$error_message = null;

// Xây dựng chuỗi VALUES cho câu lệnh INSERT MULTIPLE
$values_sql = [];
foreach ($jobs_data as $job) {
    // Làm sạch và thoát chuỗi cho từng giá trị
    $posted_by_employer_id = (int)$job[0];
    $company_name = $conn->real_escape_string($job[1]);
    $company_size = $conn->real_escape_string($job[2]);
    $job_title = $conn->real_escape_string($job[3]);
    $job_description = $conn->real_escape_string($job[4]);
    $job_benefit = $conn->real_escape_string($job[5]);
    $job_requirement = $conn->real_escape_string($job[6]);
    $job_location = $conn->real_escape_string($job[7]);
    $no_employee_needed = (int)$job[8];
    $salary = $conn->real_escape_string($job[9]);
    $post_duration = (int)$job[10];
    $contact_email = $conn->real_escape_string($job[11]);
    $contact_phone = $conn->real_escape_string($job[12]);
    $job_type = $conn->real_escape_string($job[13]);
    $job_category = $conn->real_escape_string($job[14]);
    $required_certification = $conn->real_escape_string($job[15]);
    $job_experience = $conn->real_escape_string($job[16]);
    $company_logo = $conn->real_escape_string($job[17]); // Logo đã được gán trực tiếp từ biến ở trên
    $status = $conn->real_escape_string($job[18]);

    $values_sql[] = "(
        $posted_by_employer_id,
        '$company_name',
        '$company_size',
        '$job_title',
        '$job_description',
        '$job_benefit',
        '$job_requirement',
        '$job_location',
        $no_employee_needed,
        '$salary',
        $post_duration,
        '$contact_email',
        '$contact_phone',
        '$job_type',
        '$job_category',
        '$required_certification',
        '$job_experience',
        '$company_logo',
        '$status'
    )";
}

// Xây dựng câu lệnh SQL đầy đủ
$sql = "INSERT INTO job (
    posted_by_employer_id, company_name, company_size, job_title, job_description,
    job_benefit, job_requirement, job_location, no_employee_needed, salary, post_duration,
    contact_email, contact_phone, job_type, job_category, required_certification,
    job_experience, company_logo, status
) VALUES " . implode(", ", $values_sql) . ";";

// Thực thi câu lệnh sử dụng $conn->query()
$stmt1 = $conn->query($sql);

if ($stmt1 === false) {
    // An error occurred while executing the query
    $error_message = "Error inserting data into 'job' table: {$conn->error}";
    echo $error_message;
} else {
    echo "Data inserted successfully. Rows affected: {$conn->affected_rows}";
}

// if ($error_message !== null) {
//     error_log("Job data insert error (direct query): {$error_message}");
// }