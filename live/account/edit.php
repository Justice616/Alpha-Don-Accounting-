<?php

session_start();

require_once __DIR__ . '/../src/template.php';
require_once __DIR__ . '/../src/user.php';

User::setPathDepth(1);
Template::setPathDepth(1);

User::route('account_edit');

$msg = "";
$err = "";
if (isset($_POST['submit'])) {
    $no = $_POST['no'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $subcategory = $_POST['subcategory'];
    $initial_balance = $_POST['initial_balance'];
    $normal_side = $_POST['normal_side'];

    $ret = Account::editAccount($no, $name, $category, $subcategory, User::getUserID(), $initial_balance, $normal_side);
    if ($ret == Account::EDIT_OK) {
        $msg = "Account has been updated successfully!";
    } else if ($ret == Account::EDIT_NOCHANGE) {
        $msg = "No changes were requested.";
    } else {
        $err = "Something went wrong. Please try again later.";
    }
}

if (isset($_POST['submit'])) {
    $id = $_POST['no'];
} else {
    if (!isset($_REQUEST['id'])) {
        die("invalid request.");
    }
    $id = $_REQUEST['id'];
}

$accounts = Account::getAccounts($id);
if (count($accounts) == 0) {
    die("invalid request.");
}

$account = $accounts[0];

?>
<!doctype html>
<html lang="en">
<head>
    <?=Template::header()?>
    <link rel="stylesheet" href="../public/css/style.css">

    <title><?=Template::title("Edit Account")?></title>
</head>
<body>
<?=Template::navbar()?>

<div class="container body-container">
       <div class="row justify-content-center">
           <div class="col-12 col-sm-8 col-md-6 col-lg-4 col-xl-4">


               <form action="edit.php" method="post">

                   <div class="form-group text-center">
                       <h2>Edit Account</h2>
                   </div>

                   <div class="form-group">
                       <label for="account_no" class="bmd-label-floating">Account Number</label>
                       <input name="no" type="number" class="form-control" id="account_no" value="<?=$account->id?>" readonly>
                   </div>
                   <div class="form-group">
                       <label for="account_name" class="bmd-label-floating">Account Name</label>
                       <input name="name" type="text" class="form-control" id="account_name" value="<?=$account->name?>"required>
                   </div>
                   <div class="form-group">
                       <label for="account_category" class="bmd-label-floating">Category</label>
                       <select name="category" class="custom-select" id="account_category">
                           <?php foreach(Account::getAccountCategories() as $acc) {
                               $active = '';
                               if ($acc->id == $account->category_id) {
                                   $active = ' selected';
                               } ?>
                               <option value="<?=$acc->id?>" <?=$active?>><?=$acc->name?></option>
                           <?php } // foreach $acc ?>
                       </select>
                   </div>
                   <div class="form-group">
                       <label for="account_subcategory" class="bmd-label-floating">Sub Category</label>
                       <select name="subcategory" class="custom-select" id="account_subcategory" required>
                           <option value="" selected></option>
                           <?php foreach(Account::getAccountSubCategories() as $acc) {
                               $active = '';
                               if ($acc->id == $account->subcategory_id) {
                                   $active = ' selected';
                               } ?>
                               <option value="<?=$acc->id?>"<?=$active?>><?=$acc->name?></option>
                           <?php } // foreach $acc ?>
                       </select>
                   </div>
                   <div class="form-group">
                       <label for="initial_balance" class="bmd-label-floating">Initial Balance</label>
                       <input name="initial_balance" type="number" class="form-control" id="initial_balance" step="0.01" min="0" value="<?=Template::number_format($account->initial_balance)?>">
                   </div>
                   <div class="form-group">
                       <label for="normal_side" class="bmd-label-floating">Normal Side</label>
                       <select name="normal_side" class="custom-select" id="normal_side">
                           <option value="D" <?php if ($account->normal_side == 'D') echo 'selected'; ?>>Debit</option>
                           <option value="C" <?php if ($account->normal_side == 'C') echo 'selected'; ?>>Credit</option>
                       </select>
                   </div>

                   <?php if ($msg != "") { ?>
                       <div class="alert alert-success" role="alert"><?=$msg?></div>
                   <?php } ?>
                   <?php if ($err != "") { ?>
                       <div class="alert alert-danger" role="alert"><?=$err?></div>
                   <?php } ?>

                   <div class="form-group text-center">
                       <?php if (isset($_POST['submit'])) { ?>
                           <a class="btn btn-primary btn-outline-secondary" href="../home.php">Go Back</a>
                        <?php } else { ?>
                           <a class="btn btn-primary btn-outline-secondary" href="../home.php">Cancel</a>
                        <?php } ?>
                       <button name="submit" type="submit" class="btn btn-primary btn-outline-primary">Submit</button>
                   </div>
               </form>


           </div>
       </div>
</div>

<?=Template::footer()?>
</body>
</html>
