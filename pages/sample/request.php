<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9fafb;
            margin: 0;
        }

        .content {
            margin-left: 220px; /* space for sidebar */
            padding: 20px;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
            padding: 15px 20px;
            border-bottom: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .header h1 {
            font-size: 20px;
            margin: 0;
            color: #111827;
        }

        .system-title {
            font-weight: normal;
            font-size: 16px;
            color: #6b7280;
        }

        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            max-width: 900px;
            margin: auto;
        }

        form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        label {
            font-weight: 600;
            margin-bottom: 5px;
            display: block;
            color: #374151;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
            background: #f9fafb;
        }

        textarea {
            min-height: 120px;
            grid-column: span 2; /* make purpose stretch across full width */
        }

        input[type="submit"] {
            background-color: #4F46E5;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            grid-column: span 2;
            transition: background-color 0.2s ease-in-out;
        }

        input[type="submit"]:hover {
            background-color: #4338ca;
        }

        p {
            color: #d9534f;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../sidebar.html"; ?>
    <div class="content" id="mainContent">
        <div class="header">
            <div class="hamburger" id="hamburger">☰</div>
            <h1>Disbursement Dashboard <span class="system-title">| (NAME OF DEPARTMENT)</span></h1>
            <div class="theme-toggle-container">
                <span class="theme-label">Dark Mode</span>
                <label class="theme-switch">
                    <input type="checkbox" id="themeToggle">
                    <span class="slider"></span>
                </label>
            </div>
        </div>

        <div class="form-container">
            <?php
            $conn = new mysqli("localhost","root", "", "financial");
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $requestTitle = $conn->real_escape_string($_POST['requestTitle']);
                $allocationID = (int)$_POST['allocationID'];
                $amount = (int)$_POST['amount'];
                $requestedBy = $conn->real_escape_string($_POST['requestedBy']);
                $due = $conn->real_escape_string($_POST['due']);
                $purpose = $conn->real_escape_string($_POST['purpose']);

                $sql = "INSERT INTO request (requestTitle, allocationID, Amount, Requested_by, Due, Purpose)
                        VALUES ('$requestTitle', $allocationID, $amount, '$requestedBy', '$due', '$purpose')";

                if ($conn->query($sql) === TRUE) {
                    echo "<p>New record created successfully</p>";
                } else {
                    echo "<p>Error: " . $sql . "<br>" . $conn->error . "</p>";
                }
            }

            $allocations = $conn->query("
                SELECT ch.accountName AS title, c.allocationID
                FROM costallocation c
                JOIN chartofaccount ch ON c.accountID = ch.accountID
            ");
            ?>

            <form method="POST">
                <div>
                    <label for="requestTitle">Request Title:</label>
                    <input type="text" id="requestTitle" name="requestTitle" maxlength="500" required>
                </div>

                <div>
                    <label for="allocationID">Title</label>
                    <select name="allocationID" id="allocationID" required>
                        <option value="">SELECT TITLE ON ALLOCATION ID</option>
                        <?php while ($row = $allocations->fetch_assoc()) {
                            echo "<option value='" . $row['allocationID'] . "'>" . $row['title'] . "</option>";
                        } ?>
                    </select>
                </div>

                <div>
                    <label for="amount">Amount:</label>
                    <input type="number" id="amount" name="amount" required>
                </div>

                <div>
                    <label for="requestedBy">Requested By:</label>
                    <input type="text" id="requestedBy" name="requestedBy" maxlength="500" required>
                </div>

                <div>
                    <label for="due">Due Date:</label>
                    <input type="date" id="due" name="due" required>
                </div>

                <div>
                    <label for="purpose">Purpose:</label>
                    <textarea id="purpose" name="purpose" required></textarea>
                </div>

                <input type="submit" value="Submit Request">
            </form>
            <?php $conn->close(); ?>
        </div>
    </div>
    <script src="<?php echo '../../static/js/filter.js';?>"></script>
    <script src="<?php echo '../../static/js/modal.js'; ?>"></script>
</body>
</html>
