<?php

require_once __DIR__ . '/../src/database.php';
require_once __DIR__ . '/../src/journal.php';

Class Account
{

    const STATUS_ENABLED = 0;
    const STATUS_DEACTIVATED = 1;
    const STATUS_DELETED = 2;

    const CREATE_OK = 0;
    const CREATE_EEXIST = 1;
    const CREATE_ERROR = 2;

    const EDIT_OK = 0;
    const EDIT_NOCHANGE = 1;
    const EDIT_ERROR = 2;

    /* ------------------------------------- Helper Methods --------------------------------------------------------- */

    public static function getAccountCategories()
    {
        $pdo = Database::getInstance();
        $res = $pdo->query("select * from account_categories order by id ASC");
        return $res->fetchAll(PDO::FETCH_CLASS);
    }

    public static function getAccountSubCategories()
    {
        $pdo = Database::getInstance();
        $res = $pdo->query("select * from account_subcategories order by id ASC");
        return $res->fetchAll(PDO::FETCH_CLASS);
    }

    /* ------------------------------------- Setter Methods --------------------------------------------------------- */

    public static function createAccount(
        $no,
        $name,
        $category,
        $subcategory,
        $user,
        $initial_balance,
        $normal_side) {
        $pdo = Database::getInstance();
        $query = <<<EOF
insert into 
  accounts
  (id, name, category, subcategory, user_added, initial_balance, normal_side)
VALUES 
  (?, ?, ?, ?, ?, ?, ?);
EOF;
        try {
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(1, $no, PDO::PARAM_INT);
            $stmt->bindParam(2, $name, PDO::PARAM_STR);
            $stmt->bindParam(3, $category, PDO::PARAM_INT);
            $stmt->bindParam(4, $subcategory, PDO::PARAM_INT);
            $stmt->bindParam(5, $user, PDO::PARAM_INT);
            $stmt->bindParam(6, $initial_balance, PDO::PARAM_STR);
            $stmt->bindParam(7, $normal_side, PDO::PARAM_STR);

            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                return self::CREATE_OK;
            }
        } catch (Exception $e) {
            if ($e->getCode() == 23000) {
                return self::CREATE_EEXIST;
            }
        }
        return self::CREATE_ERROR;
    }

    public static function editAccount(
        $no,
        $name,
        $category,
        $subcategory,
        $user,
        $initial_balance,
        $normal_side) {
        $pdo = Database::getInstance();
        $query = <<<EOF
update accounts
set
  name = ?,
  category = ?,
  subcategory = ?,
  initial_balance = ?,
  normal_side = ?  
WHERE 
  id = ?
EOF;
        try {
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(1, $name, PDO::PARAM_STR);
            $stmt->bindParam(2, $category, PDO::PARAM_INT);
            $stmt->bindParam(3, $subcategory, PDO::PARAM_INT);
            $stmt->bindParam(4, $initial_balance, PDO::PARAM_STR);
            $stmt->bindParam(5, $normal_side, PDO::PARAM_STR);
            $stmt->bindParam(6, $no, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                return self::EDIT_NOCHANGE;
            } else {
                return self::EDIT_OK;
            }
        } catch (Exception $e) {
            return self::EDIT_ERROR;
        }
        return self::CREATE_ERROR;
    }

    public static function activateAccount($id, $status = self::STATUS_DEACTIVATED) {
        $pdo = Database::getInstance();
        try {
            $stmt = $pdo->prepare("update accounts set status = ? where id = ?");
            $stmt->bindParam(1, $status, PDO::PARAM_INT);
            $stmt->bindParam(2, $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                ; // this is good.
            } else {
                ; // umm ?
            }
        } catch (Exception $e) {
            die("something went wrong. please go back and try again.");
        }
    }

    /* ------------------------------------- Getter Methods --------------------------------------------------------- */

    public static function getAccounts($id = FALSE, $account_status = FALSE) {
        $query = <<<EOF
select
  a.id,
  a.name,
  c.name as category,
  s.name as subcategory,
  a.initial_balance,
  a.normal_side,
  a.status,
  a.created,
  a.modified,
  u.name as added,
  a.category as category_id,
  a.subcategory as subcategory_id
from
  accounts a
inner join
  account_categories c
  on
    a.category = c.id
inner join
  account_subcategories s
  on
    a.subcategory = s.id
inner join
  users u
  on
    a.user_added = u.id
where {{where}}
order by a.created desc
EOF;

        $binds = array();

        // queries
        if ($id !== FALSE) {
            $query = str_replace("{{where}}", "a.id = ? and {{where}}", $query);
            $binds[] = $id;
        }
        if ($account_status !== FALSE) {
            $query = str_replace("{{where}}", "a.status = ? and {{where}}", $query);
            $binds[] = $account_status;
        }

        // finalize
        $query = str_replace("{{where}}", "1", $query);

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare($query);

        $stmt->execute($binds);
        return $stmt->fetchAll(PDO::FETCH_CLASS);
    }

    public static function getAccountBalance($id) {
        $query = <<<EOF
select
  sum(e.debit) as total_debit,
  sum(e.credit) as total_credit
from
  journal_entries e
inner join
  journal_index j
  on
    e.journal_ref = j.ref
where
  j.status = ? and
  e.account_id = ?;
EOF;
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare($query);
        $stmt->execute([Journal::STATE_POSTED, $id]);
        return $stmt->fetch();
    }

} // Account
