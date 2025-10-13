<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добро пожаловать в PastelFinance</title>
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            background: #f2f0eb;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            text-align: center;
            background: #fffaf8;
            padding: 3rem 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            width: 90%;
            max-width: 1100px;
        }

        h1 {
            color: #4b453f;
            font-size: 2.2rem;
            margin-bottom: 0.75rem;
        }

        p {
            color: #6b655d;
            font-size: 1rem;
            line-height: 1.6;
            max-width: 750px;
            margin: 0 auto 2rem;
        }

        .cards {
            display: flex;
            justify-content: space-between;
            gap: 1.5rem;
            margin-top: 2rem;
            margin-bottom: 2.5rem;
        }

        .card {
            background: #f9f6f2;
            padding: 1.5rem;
            flex: 1 1 calc(33.333% - 1rem);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            min-width: 250px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .card h3 {
            color: #5a5045;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .card p {
            color: #7a7266;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .buttons {
            margin-top: 1rem;
        }

        .btn {
            padding: 0.75rem 1.75rem;
            border: none;
            border-radius: 30px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0 0.75rem;
        }

        .btn-primary {
            background-color: #a3c9c9;
            color: #5e5e5e;
            box-shadow: 0 4px 10px rgba(215, 196, 242, 0.3);
        }

        .btn-primary:hover {
            background-color: #737272;
            box-shadow: 0 6px 15px rgba(94, 94, 94, 0.4);
        }

        .btn-secondary {
            background-color: #e8e4de;
            color: #5a5045;
        }

        .btn-secondary:hover {
            background-color: #ded8ce;
        }

        @media (max-width: 900px) {
            .cards {
                flex-direction: column;
                align-items: center;
            }

            .card {
                width: 100%;
                max-width: 350px;
            }

            h1 {
                font-size: 1.8rem;
            }

            p {
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Добро пожаловать в <span style="color:#393939;">PastelFinance</span></h1>
    <p>Управляйте своими финансами легко и приятно. Планируйте бюджет, отслеживайте расходы и анализируйте доходы в одном месте.</p>

    <div class="cards">
        <div class="card">
            <h3>Инструменты для бюджетирования</h3>
            <p>Создавайте бюджеты и следите за их выполнением, чтобы достигать финансовых целей.</p>
        </div>
        <div class="card">
            <h3>Отслеживание расходов</h3>
            <p>Просматривайте, куда уходят ваши деньги, с помощью удобной аналитики и отчетов.</p>
        </div>
        <div class="card">
            <h3>Анализ доходов</h3>
            <p>Получайте прозрачную картину своих источников доходов и динамики изменений.</p>
        </div>
    </div>

    <div class="buttons">
        <a href="register"><button class="btn btn-primary">Зарегистрироваться</button></a>
        <a href="login"><button class="btn btn-secondary">Войти</button></a>
    </div>
</div>
</body>
</html>
