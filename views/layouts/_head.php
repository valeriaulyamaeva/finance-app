<?php
use yii\helpers\Html;

$this->registerMetaTag(['charset' => Yii::$app->charset]);
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1']);
?>
<title><?= Html::encode($this->title) ?></title>
<?php $this->head() ?>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

    :root {
        --card-bg-light: #ffffff;
        --card-bg-dark: #1f1f1f;
    }


    body.theme-light {
        --card-bg: var(--card-bg-light);
    }

    body.theme-dark {
        --card-bg: var(--card-bg-dark);
    }


    body.theme-light {
        background-color: #f9f7f4;
        color: #4b453f;
    }

    body.theme-light header,
    body.theme-light footer {
        background-color: #000;
        color: #fff;
    }

    body.theme-light .sidebar {
        background-color: #b6b6b6;
        color: #1c1b1b;
    }

    body.theme-light .summary-card,
    body.theme-light .transaction-card,
    body.theme-light .card,
    body.theme-light .modal-header,
    body.theme-light .modal-body,
    body.theme-light .modal-footer {
        background-color: #fff;
        color: #4b453f;
    }

    body.theme-light .btn-add,
    body.theme-light .btn-primary {
        background-color: #a3c9c9;
        color: #222020;
    }

    body.theme-light .btn-add:hover,
    body.theme-light .btn-primary:hover {
        background-color: #8da4a4;
        color: #fff;
    }

    body.theme-light .btn-secondary {
        background-color: #f3f4f6;
        color: #4b453f;
    }

    body.theme-light .btn-secondary:hover {
        background-color: #e5e7eb;
    }

    body.theme-light .form-control,
    body.theme-light .form-select,
    body.theme-light textarea {
        background-color: #fff;
        color: #222;
        border: 1px solid #d1d5db;
    }

    body.theme-light .form-control::placeholder,
    body.theme-light textarea::placeholder {
        color: #9ca3af;
    }

    body.theme-dark {
        background-color: #121212;
        color: #e0e0e0;
    }

    body.theme-dark .sidebar {
        background-color: #1e1e1e;
        color: #ddd;
    }

    body.theme-dark .sidebar h2 {
        color: #f5f5f5;
    }

    body.theme-dark .sidebar ul li a {
        color: #ccc;
    }

    body.theme-dark .sidebar ul li a:hover {
        color: #fff;
    }

    body.theme-dark .summary-card,
    body.theme-dark .transaction-card,
    body.theme-dark .card,
    body.theme-dark .modal-header,
    body.theme-dark .modal-body,
    body.theme-dark .modal-footer {
        background-color: #1f1f1f;
        color: #ddd;
        box-shadow: 0 4px 12px rgba(255,255,255,0.05);
    }

    body.theme-dark .btn-add,
    body.theme-dark .btn-primary {
        background-color: #3aa6a6;
        color: #fff;
    }

    body.theme-dark .btn-add:hover,
    body.theme-dark .btn-primary:hover {
        background-color: #2c8888;
    }

    body.theme-dark .btn-secondary {
        background-color: #2a2a2a;
        color: #ccc;
    }

    body.theme-dark .btn-secondary:hover {
        background-color: #3a3a3a;
    }

    body.theme-dark .transaction-actions button {
        color: #a1a1a1;
    }

    body.theme-dark .transaction-actions button:hover {
        color: #fff;
    }

    body.theme-dark .form-control,
    body.theme-dark .form-select,
    body.theme-dark textarea {
        background-color: #2a2a2a;
        color: #e5e5e5;
        border: 1px solid #3d3d3d;
    }

    body.theme-dark .form-control::placeholder,
    body.theme-dark textarea::placeholder {
        color: #9ca3af;
    }

    body.theme-dark .form-control:focus,
    body.theme-dark .form-select:focus,
    body.theme-dark textarea:focus {
        border-color: #3aa6a6;
        box-shadow: 0 0 0 2px rgba(58,166,166,0.3);
        outline: none;
    }

    body.theme-dark .form-control:disabled,
    body.theme-dark .form-select:disabled,
    body.theme-dark textarea:disabled {
        background-color: #1f1f1f;
        color: #777;
    }
</style>
