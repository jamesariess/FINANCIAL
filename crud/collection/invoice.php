<?php





if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['employeecrud'])){
    $custumerID = $_POST['CustomerID']:" ";
    $issudate = $_POST['issueDate']:" ";
    $duedate = $_POST['dueDate']:" ";
    $reference = $_POST['reference']: " ";
    $amount = $_POST['amount']:" ";
    $status = $_POST['status']:" ";
    $balance = $_POST['balance']:" ";
    $reference = $_POST['reference']: " ";


    if(empty($custumerID)||empty($issudate)||empty($duedate)||empty($reference)||empty($amount)||empty($status)||empty($balance)){
        $error = "All fields are required.";
    }else{
        $result = $crud->createInvoice($custumerID,
         $issudate, $duedate, $reference, $amount, $status, $balance);
        if($result){
            $success = "Invoice created successfully.";
        }else{
            $error = "Failed to create invoice.";

    }
    }
}






?>