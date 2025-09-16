<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
        }
        label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
        }
        input[type="text"], input[type="number"], input[type="date"], textarea, select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            height: 100px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        p {
            color: #d9534f;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <?php
        $conn = new mysqli("localhost", "rofina_financesot", "7rO-@mwup07Io^g0", "fina_financial");
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
    

            $sql = "INSERT INTO request (requestTitle, allocationID, Amount, Requested_by, Due, Purpuse)
                    VALUES ('$requestTitle', $allocationID, $amount, '$requestedBy', '$due',  '$purpose')";

            if ($conn->query($sql) === TRUE) {
                echo "<p>New record created successfully</p>";
            } else {
                echo "<p>Error: " . $sql . "<br>" . $conn->error . "</p>";
            }
        }

        // Fetch allocation IDs and titles from costallocation table
        $allocations = $conn->query("SELECT allocationID, title FROM costallocation");
        ?>

        <form method="POST">
            <label for="requestTitle">Request Title:</label><br>
            <input type="text" id="requestTitle" name="requestTitle" maxlength="500" required><br><br>

            <label for="allocationID">Title</label>
            <select name="allocationID" id="allocationID" required>
                <option value="">SELECT TITLE ON ALLOCATION ID</option>
                <?php
                while ($row = $allocations->fetch_assoc()) {
                    echo "<option value='" . $row['allocationID'] . "'>" . $row['title'] . "</option>";
                }
                ?>
            </select><br><br>

            <label for="amount">Amount:</label><br>
            <input type="number" id="amount" name="amount" required><br><br>

            <label for="requestedBy">Requested By:</label><br>
            <input type="text" id="requestedBy" name="requestedBy" maxlength="500" required><br><br>

            <label for="due">Due Date:</label><br>
            <input type="date" id="due" name="due" required><br><br>

            <label for="purpose">Purpose:</label><br>
            <textarea id="purpose" name="purpose" required></textarea><br><br>

      

            <input type="submit" value="Submit Request">
        </form>
        <?php $conn->close(); ?>
    </div>
</body>
</html>