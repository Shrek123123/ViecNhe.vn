<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Register - JobHive</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f3f3f3;
        }

        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            box-sizing: border-box;
        }

        .register-form-container {
            background: #ffffff;
            color: #8B0000;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            text-align: center;
            box-sizing: border-box;
        }

        h2 {
            color: #d90000;
            margin-bottom: 10px;
        }

        p {
            margin-top: 0;
            margin-bottom: 25px;
            color: #555;
        }

        h3 {
            color: #333;
            text-align: left;
            margin-top: 25px;
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            background: #fff;
        }

        input[type="checkbox"] {
            width: auto;
            margin-right: 5px;
            padding: 0;
        }

        .btn-register {
            width: 100%;
            padding: 12px;
            background-color: #d90000;
            color: #ffffff;
            font-size: 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .login-link,
        .redirect-jobseeker {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .login-link a,
        .redirect-jobseeker a {
            color: #d90000;
            text-decoration: none;
            font-weight: bold;
        }

        .terms-container {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        #terms-error {
            color: red;
            font-size: 0.9em;
            margin-top: 5px;
            display: none;
        }

        .error-tooltip {
            position: absolute;
            background-color: #f8d7da;
            color: #721c24;
            padding: 5px;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            margin-top: 5px;
            font-size: 12px;
            z-index: 10;
            left: 0;
            right: 0;
            text-align: left;
        }
    </style>
</head>

<body>
    <?php
    include_once(__DIR__ . '/header_loginlogout/employer.php');
    ?>

    <div class="main-content">
        <div class="register-form-container">
            <h2><?php echo $lang['employerregister_title']; ?></h2>
            <p><?php echo $lang['employerregister_subtitle']; ?></p>
            <form action="employerregister.php" method="post">
                <h3><?php echo $lang['account_information']; ?></h3>
                <div class="form-group">
                    <label><?php echo $lang['username']; ?></label>
                    <input type="text" placeholder="<?php echo $lang['placeholder_username']; ?>" name="username"
                        required>
                </div>
                <div class="form-group">
                    <label><?php echo $lang['email']; ?></label>
                    <input type="email" placeholder="<?php echo $lang['placeholder_email']; ?>" name="email" required>
                </div>
                <div class="form-group">
                    <label><?php echo $lang['password']; ?></label>
                    <input type="password" placeholder="<?php echo $lang['placeholder_password']; ?>" name="password"
                        required>
                </div>
                <div class="form-group">
                    <label><?php echo $lang['reenter_password']; ?></label>
                    <input type="password" placeholder="<?php echo $lang['placeholder_reenter_password']; ?>"
                        name="retypepass" required>
                </div>

                <h3><?php echo $lang['company_information']; ?></h3>
                <div class="form-group">
                    <label><?php echo $lang['company_name']; ?></label>
                    <input type="text" placeholder="<?php echo $lang['placeholder_company_name']; ?>" name="companyname"
                        required>
                </div>
                <div class="form-group">
                    <label><?php echo $lang['company_address']; ?></label>
                    <input type="text" placeholder="<?php echo $lang['placeholder_company_address']; ?>"
                        name="companyaddress" required>
                </div>
                <div class="form-group">
                    <label><?php echo $lang['field_of_work']; ?></label>
                    <select name="industry" required>
                        <option value=""><?php echo $lang['choose_industry']; ?></option>
                        <option value="IT"><?php echo $lang['industry_it_software']; ?></option>
                        <option value="Marketing"><?php echo $lang['industry_marketing']; ?></option>
                        <option value="Finance"><?php echo $lang['industry_finance']; ?></option>
                        <option value="Healthcare"><?php echo $lang['industry_healthcare']; ?></option>
                        <option value="Government & Public Sector"><?php echo $lang['industry_government']; ?></option>
                        <option value="Education"><?php echo $lang['industry_education']; ?></option>
                        <option value="Manufacturing"><?php echo $lang['industry_manufacturing']; ?></option>
                        <option value="Retail"><?php echo $lang['industry_retail']; ?></option>
                        <option value="Hospitality"><?php echo $lang['industry_hospitality']; ?></option>
                        <option value="Telecommunications"><?php echo $lang['industry_telecommunications']; ?></option>
                        <option value="Construction"><?php echo $lang['industry_construction']; ?></option>
                        <option value="Media & Entertainment"><?php echo $lang['industry_media']; ?></option>
                        <option value="Transportation & Logistics"><?php echo $lang['industry_transportation']; ?>
                        </option>
                        <option value="Energy & Utilities"><?php echo $lang['industry_energy']; ?></option>
                        <option value="Real Estate"><?php echo $lang['industry_real_estate']; ?></option>
                        <option value="Agriculture"><?php echo $lang['industry_agriculture']; ?></option>
                        <option value="Consulting"><?php echo $lang['industry_consulting']; ?></option>
                        <option value="Non-profit"><?php echo $lang['industry_non_profit']; ?></option>
                        <option value="Others"><?php echo $lang['industry_others']; ?></option>
                    </select>
                </div>

                <div class="terms-container" style="align-items: center;">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" name="terms" value="accepted" required style="margin: 0;">
                        <small><?php echo $lang['terms_agreement_text']; ?> <a
                                href="term.html"><?php echo $lang['terms_of_use_link_text']; ?></a>
                            <?php echo $lang['terms_of_use_after_link_text']; ?></small>
                    </label>
                    <span id="terms-error"></span>
                </div>
                <button type="submit" class="btn-register"><?php echo $lang['button_register']; ?></button>
            </form>

            <div class="login-link">
                <?php echo $lang['login_link_text_prefix']; ?> <a
                    href="employerlogin.php"><?php echo $lang['login_link_text']; ?></a>
            </div>
            <div class="redirect-jobseeker">
                <p><?php echo $lang['jobseeker_redirect_text_prefix']; ?> <a
                        href="jobseekerregister.php"><?php echo $lang['jobseeker_redirect_link_text']; ?></a></p>
            </div>
        </div>
    </div>
</body>

</html>

<?php
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $retypepass = $_POST['retypepass'];
    $companyname = $_POST['companyname'];
    $companyaddress = $_POST['companyaddress'];
    $industry = $_POST['industry'];

    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($retypepass) || empty($companyname) || empty($companyaddress) || empty($industry)) {
        echo "<script>
            alert('" . addslashes($lang['all_fields_required']) . "');
            window.history.back();
            </script>";
        exit;
    }

    if ($password !== $retypepass) {
        echo "<script>
            const passwordField = document.querySelector('input[name=\"password\"]');
            const errorTooltip = document.createElement('div');
            errorTooltip.textContent = '" . addslashes($lang['passwords_do_not_match']) . "';
            errorTooltip.className = 'error-tooltip';
            passwordField.parentNode.appendChild(errorTooltip);
            passwordField.focus();

            setTimeout(() => {
                errorTooltip.remove();
            }, 3000);
            </script>";
        exit;
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>
            const emailField = document.querySelector('input[name=\"email\"]');
            const errorTooltip = document.createElement('div');
            errorTooltip.textContent = '" . addslashes($lang['email_already_exists']) . "';
            errorTooltip.className = 'error-tooltip';
            emailField.parentNode.appendChild(errorTooltip);
            emailField.focus();

            setTimeout(() => {
                errorTooltip.remove();
            }, 3000);
            </script>";
        exit;
    }

    // Insert new user into the database
    // Insert user first
    $stmt = $conn->prepare("INSERT INTO user (username, user_type, email, password) VALUES (?, 'employer', ?, ?)");
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt->bind_param("sss", $username, $email, $hashed_password);
    $stmt->execute();

    // Get the ID of the newly created user
    $user_id = $conn->insert_id;

    // Only insert into the company table if user_type is 'employer'
    if ($user_id) {
        $stmt_company = $conn->prepare("INSERT INTO company (employer_id, company_name, company_address, industry) VALUES (?, ?, ?, ?)");
        $stmt_company->bind_param("isss", $user_id, $companyname, $companyaddress, $industry);
        $stmt_company->execute();
        $stmt_company->close();
    }

    if ($stmt->affected_rows > 0) {
        $stmt->close();
        echo "<script>
                alert('" . addslashes($lang['registration_successful']) . "');
                window.location.href = 'employerlogin.php';
            </script>";
        exit;
    } else {
        $stmt->close();
        echo "<script>
                alert('" . addslashes($lang['error_prefix'] . $stmt->error) . "');
                window.history.back();
            </script>";
        exit;
    }
}
$conn->close();
?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form');
        const termsCheckbox = document.querySelector('input[name="terms"]');
        const termsError = document.getElementById('terms-error');

        form.addEventListener('submit', function (event) {
            if (!termsCheckbox.checked) {
                event.preventDefault();
                termsError.textContent = '<?php echo addslashes($lang['terms_not_checked']); ?>';
                termsError.style.display = 'block';

                setTimeout(function () {
                    termsError.style.display = 'none';
                    termsError.textContent = '';
                }, 3000);
            } else {
                termsError.style.display = 'none';
            }
        });
    });
</script>