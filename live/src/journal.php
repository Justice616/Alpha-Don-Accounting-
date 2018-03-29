<?php

require_once __DIR__ . '/../src/database.php';

class Journal {

    const CREATE_OK = 0;
    const CREATE_ERROR = 1;

    const STATE_PENDING = 0;
    const STATE_REJECTED = 1;
    const STATE_POSTED = 2;

    /* ------------------------------------- Setter Methods --------------------------------------------------------- */


    public static function createJournalEntry($user, $date, $entries, $desc = "", $files = array()) {
        $files_e = serialize($files);
        $pdo = Database::getInstance();

        $query_journal_index = <<<EOF
insert into
  journal_index
    (user, entry_date, description, files)
VALUES
  (?, ?, ?, ?);
EOF;
        $query_journal_entries = <<<EOF
insert into
  journal_entries
    (journal_ref, account_id, debit, credit)
VALUES
  (?, ?, ?, ?);
EOF;

        try {
            $pdo->beginTransaction();

            $stmt_idx = $pdo->prepare($query_journal_index);
            $stmt_idx->execute([$user, $date, $desc, $files_e]);

            $journal_ref = $pdo->lastInsertId();

            $stmt_entries = $pdo->prepare($query_journal_entries);
            foreach($entries as $e) {
                $account = $e[0];
                $debit = (float) $e[1];
                $credit = (float) $e[2];

                $stmt_entries->execute([$journal_ref, $account, $debit, $credit]);
            }

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollback();
            return self::CREATE_ERROR;
        }
        return self::CREATE_OK;
    }

    public static function getJournalEntry($ref) {
        $q_idx = <<<EOF
select
  j.ref,
  j.user,
  u.name as user_name,
  j.entry_date,
  j.description,
  j.description as j_desc,
  j.files as files_e,
  j.status,
  j.response,
  j.created,
  DATE_FORMAT(j.entry_date, '%Y-%m-%d %h:%i %p') as entry_date_formatted
from
  journal_index j
inner join
  users u
  on j.user = u.id
where
  j.ref = ?;
EOF;
        $q_entries = <<<EOF
select
  e.id,
  e.account_id,
  a.name as account_name,
  e.debit,
  e.credit
from
  journal_entries e
inner join
  accounts a
  on
    e.account_id = a.id
where
  e.journal_ref = ?;
EOF;
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare($q_idx);
            $stmt->execute([$ref]);
            $res = $stmt->fetchAll();

            if (count($res) == 0) {
                return FALSE;
            }

            $ret = $res[0];

            $ret['files'] = unserialize($ret['files_e']);

            $stmt = $pdo->prepare($q_entries);
            $stmt->execute([$ref]);
            $entries = $stmt->fetchAll();

            $ret['entries'] = $entries;

            return $ret;
        } catch (Exception $e) {
            die("something went wrong. please go back and try again.");
        }
        return FALSE;
    }

    public static function getJournalEntries($status = FALSE, $user = FALSE) {
        $query = <<<EOF
select
  j.*,
  DATE_FORMAT(j.entry_date, '%Y-%m-%d %h:%i %p') as entry_date_formatted
from
  journal_index j
WHERE {{where}}
order BY 
  created desc
EOF;

        $binds = array();

        if ($status !== FALSE) {
            $query = str_replace("{{where}}", "status = ? and {{where}}", $query);
            $binds[] = $status;
        }

        if ($user !== FALSE) {
            $query = str_replace("{{where}}", "user = ? and {{where}}", $query);
            $binds[] = $user;
        }

        // finalize
        $query = str_replace("{{where}}", "1", $query);

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare($query);

        $stmt->execute($binds);
        return $stmt->fetchAll();
    }

    public static function getJournalEntriesByAccount($id) {
        $query = <<<EOF
select
  distinct(e.journal_ref) as ref,
  j.*,
  DATE_FORMAT(j.entry_date, '%Y-%m-%d %h:%i %p') as entry_date_formatted
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
        $stmt->execute([self::STATE_POSTED, $id]);
        return $stmt->fetchAll();
    }

    public static function setJournalEntryStatus($ref, $status = self::STATE_PENDING, $response = NULL) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("update journal_index set status = ?, response = ? where ref = ?");
        $stmt->execute([$status, $response, $ref]);
    }

}
