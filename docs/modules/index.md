# Модули

Основные функциональные модули приложения. Каждый имеет controller + service + form + view.

| Модуль | Controller | Service | Описание |
|--------|-----------|---------|----------|
| [[transactions]] | TransactionController | [[../services/TransactionService]] | Доходы/расходы |
| [[budgets]] | BudgetController | [[../services/BudgetService]] | Лимиты по категориям |
| [[categories]] | CategoryController | CategoryService | Классификация |
| [[goals]] | GoalController | GoalService | Цели накоплений |
| [[recurring]] | RecurringTransactionController | RecurringTransactionService | Повторяющиеся платежи |
| [[analytics]] | AnalyticsController | — | Статистика и графики |
| [[notifications]] | NotificationController | [[../services/NotificationService]] | Алерты |
| [[import]] | ImportController | [[../services/ImportService]] | Импорт выписок |
| [[settings]] | SettingsController | UserService | Профиль/тема/валюта |
