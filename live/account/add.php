<?php

session_start();

require_once __DIR__ . '/../src/template.php';
require_once __DIR__ . '/../src/user.php';

User::setPathDepth(1);
Template::setPathDepth(1);

User::route('account_add');

$msg = "";
$err = "";
if (isset($_POST['submit'])) {
    $no = $_POST['no'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $subcategory = $_POST['subcategory'];
    $initial_balance = $_POST['initial_balance'];
    $normal_side = $_POST['normal_side'];

    $ret = Account::createAccount($no, $name, $category, $subcategory, User::getUserID(), $initial_balance, $normal_side);
    if ($ret == Account::CREATE_OK) {
        $msg = "Account has been created!";
    } else if ($ret == Account::CREATE_EEXIST) {
        $err = "Account Number or Name already exist!";
    } else {
        $err = "Something went wrong. Please try again later.";
    }
}

?>
<!doctype html>
<html lang="en">
<head>
    <?=Template::header()?>
    <link rel="stylesheet" href="../public/css/style.css">

    <title><?=Template::title("Add Account")?></title>
</head>
<body>
<?=Template::navbar()?>

<div class="container body-container">
       <div class="row justify-content-center">
           <div class="col-12 col-sm-8 col-md-6 col-lg-4 col-xl-4">


               <form action="add.php" method="post">

                   <div class="form-group text-center">
                       <h2>Add Account</h2>
                   </div>

                   <div class="form-group">
                       <label for="account_no" class="bmd-label-floating">Account Number</label>
                       <input name="no" type="number" class="form-control" id="account_no" required>
                   </div>
                   <div class="form-group">
                       <label for="account_name" class="bmd-label-floating">Account Name</label>
                       <input name="name" type="text" class="form-control" id="account_name" required>
                   </div>
                   <div class="form-group">
                       <label for="account_category" class="bmd-label-floating">Category</label>
                       <select name="category" class="custom-select" id="account_category" required>
                           <option value="" selected></option>
                           <?php foreach(Account::getAccountCategories() as $acc) { ?>
                               <option value="<?=$acc->id?>"><?=$acc->name?></option>
                           <?php } // foreach $acc ?>
                       </select>
                   </div>
                   <div class="form-group">
                       <label for="account_subcategory" class="bmd-label-floating">Sub Category</label>
                       <select name="subcategory" class="custom-select" id="account_subcategory" required>
                           <option value="" selected></option>
                           <?php foreach(Account::getAccountSubCategories() as $acc) { ?>
                               <option value="<?=$acc->id?>"><?=$acc->name?></option>
                           <?php } // foreach $acc ?>
                       </select>
                   </div>
                   <div class="form-group">
                       <label for="initial_balance" class="bmd-label-floating">Initial Balance</label>
                       <input name="initial_balance" type="number" class="form-control" id="initial_balance" step="0.01" min="0" value="0.00">
                   </div>
                   <div class="form-group">
                       <label for="normal_side" class="bmd-label-floating">Normal Side</label>
                       <select name="normal_side" class="custom-select" id="normal_side" required>
                           <option value="D">Debit</option>
                           <option value="C">Credit</option>
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
