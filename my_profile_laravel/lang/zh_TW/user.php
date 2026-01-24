<?php

declare(strict_types=1);

return [
    'title' => '使用者管理',
    'single' => '使用者',
    'plural' => '使用者',

    'fields' => [
        'id' => 'ID',
        'name' => '姓名',
        'username' => '使用者名稱',
        'email' => 'Email',
        'password' => '密碼',
        'role' => '角色',
        'status' => '帳號狀態',
        'salesperson_status' => '業務員狀態',
        'is_paid_member' => '付費會員',
        'email_verified_at' => 'Email 驗證時間',
        'created_at' => '註冊時間',
        'updated_at' => '最後更新時間',
        'deleted_at' => '刪除時間',
        'salesperson_applied_at' => '申請時間',
        'salesperson_approved_at' => '審核時間',
        'rejection_reason' => '拒絕原因',
        'can_reapply_at' => '可重新申請時間',
    ],

    'roles' => [
        'user' => '一般使用者',
        'salesperson' => '業務員',
        'admin' => '管理員',
    ],

    'statuses' => [
        'active' => '啟用',
        'inactive' => '停用',
    ],

    'salesperson_statuses' => [
        'pending' => '待審核',
        'approved' => '已批准',
        'rejected' => '已拒絕',
    ],

    'actions' => [
        'create' => '建立使用者',
        'edit' => '編輯使用者',
        'view' => '檢視使用者',
        'delete' => '刪除使用者',
        'toggle_status' => '切換狀態',
        'activate' => '啟用',
        'deactivate' => '停用',
        'reset_password' => '重設密碼',
        'change_role' => '變更角色',
        'bulk_activate' => '批量啟用',
        'bulk_deactivate' => '批量停用',
    ],

    'messages' => [
        'created' => '使用者建立成功',
        'updated' => '使用者更新成功',
        'deleted' => '使用者刪除成功',
        'activated' => ':name 的帳號已啟用',
        'deactivated' => ':name 的帳號已停用',
        'role_changed' => '角色變更成功',
        'password_reset_sent' => '密碼重設郵件已發送',
        'password_reset_failed' => '無法發送密碼重設郵件，請稍後再試',
        'bulk_activated' => '已啟用 :count 個使用者帳號',
        'bulk_deactivated' => '已停用 :count 個使用者帳號',
    ],

    'filters' => [
        'role' => '角色',
        'status' => '帳號狀態',
        'salesperson_status' => '業務員狀態',
        'created_at' => '註冊時間範圍',
        'email_verified' => 'Email 已驗證',
    ],

    'sections' => [
        'basic_info' => '基本資訊',
        'role_permissions' => '角色與權限',
        'account_status' => '帳號狀態',
        'salesperson_details' => '業務員詳細資訊',
        'time_records' => '時間記錄',
    ],

    'helpers' => [
        'password_min_8' => '最少 8 個字元。留空則不變更密碼。',
        'email_verified_info' => '設定此時間表示 Email 已驗證',
        'salesperson_status_hint' => '只有角色為業務員時才需要設定',
        'username_auto' => '未設定時將使用 Email 前綴',
    ],

    'confirmations' => [
        'toggle_status' => '確定要:action :name 的帳號嗎？',
        'reset_password' => '確定要發送密碼重設郵件給 :email 嗎？',
        'delete' => '確定要刪除此使用者嗎？',
    ],
];
