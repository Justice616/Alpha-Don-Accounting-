<?php

require_once __DIR__ . '/../src/user.php';

class Template {
    const COMPANY_NAME = "AD Accounting";

    public static $path_depth = 0;

    public static function setPathDepth($depth) {
        self::$path_depth = $depth;
    }

    public static function header()
    {
        $html = <<<EOF
<meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <!-- Material Design for Bootstrap fonts and icons -->
        <link rel="stylesheet" href="{depth}public/css/fonts.css">

        <!-- Material Design for Bootstrap CSS -->
        <link rel="stylesheet" href="{depth}public/css/bootstrap-material-design.min.css">

        <link rel="stylesheet" href="{depth}public/fa/css/fa-solid.min.css">
        <link rel="stylesheet" href="{depth}public/fa/css/fontawesome.min.css">
EOF;

        $html = str_replace("{depth}", str_repeat("../", self::$path_depth), $html);
        return $html;
    }

    public static function footer() {
        $html =  <<<EOF
<script src="{depth}public/js/jquery-3.2.1.min.js"></script>
        <script src="{depth}public/js/popper.min.js"></script>
        <script src="{depth}public/js/snackbar.min.js"></script>
        <script src="{depth}public/js/bootstrap-material-design.js"></script>
        <script>$(document).ready(function() { $('body').bootstrapMaterialDesign(); });</script>
EOF;

        $html = str_replace("{depth}", str_repeat("../", self::$path_depth), $html);
        return $html;
    }

    public static function title($title)
    {
        if (!isset($title) || trim($title) == '') {
            return self::COMPANY_NAME;
        }

        return sprintf("[ %s ] - %s", $title, self::COMPANY_NAME);
    }

    public static function navbar() {
        $depth = str_repeat('../', self::$path_depth);
        $company = self::COMPANY_NAME;

        $username = User::getUserName();
        $usertitle = User::getUserTitle();

        $userLinks = "";
        if (User::getUserType() == User::TYPE_USER) {
            $userLinks = <<<EOF
                <li class="nav-item">
                    <a class="nav-link" href="{$depth}journal/add.php">
                        <span><i class="fas fa-plus"></i> Add Journal</span>
                    </a>
                </li>
EOF;
        }


        $html = <<<EOF
<nav class="navbar navbar-expand-lg navbar-light">
    <a class="navbar-brand">{$company}</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="{$depth}home.php">
                    Home
                </a>
            </li>
            {$userLinks}
            <li class="nav-item">
                <a class="nav-link" href="{$depth}journal/view.php">
                    <span><i class="fas fa-eye"></i> View Entries</span>
                </a>
            </li>
        </ul>
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link">
                    <i class="fas fa-user-circle"></i>
                    {$username}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link">
                    <i class="fas fa-address-card"></i>
                    {$usertitle}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{$depth}do.Logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>
</nav>
EOF;
        return $html;
    }

    function number_format($number) {
        return number_format($number, 2, '.', '');
    }

}
