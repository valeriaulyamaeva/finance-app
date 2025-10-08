$(function () {
    const modal = new bootstrap.Modal($('#budgetModal'));
    let currentId = null;

    $('#addBudgetBtn').on('click', function () {
        $('#budgetForm')[0].reset();
        currentId = null;
        $('.modal-title').text('Новый бюджет');
        modal.show();
    });

    $('#saveBudgetBtn').on('click', function () {
        // Данные берём ТОЛЬКО при нажатии "Сохранить"
        const data = {
            name: $('#budgetName').val(),
            amount: $('#budgetAmount').val(),
            period: $('#budgetPeriod').val(),
            start_date: $('#budgetStartDate').val(),
            end_date: $('#budgetEndDate').val(),
            category_id: $('#budgetCategory').val()
        };

        // Проверим в консоли, что категория реально передаётся
        console.log('Отправляемые данные:', data);

        const url = currentId ? `/budget/update?id=${currentId}` : '/budget/create';
        $.post(url, data)
            .done(resp => {
                if (resp.success) {
                    location.reload();
                } else {
                    alert(resp.message || 'Ошибка при сохранении');
                }
            })
            .fail(xhr => {
                console.error('Ошибка при создании бюджета:', xhr.responseText);
                alert('Ошибка при создании бюджета: ' + xhr.responseText);
            });
    });

    $('.editBtn').on('click', function () {
        const card = $(this).closest('.card');
        currentId = card.data('id');
        $('#budgetName').val(card.find('h5').text());
        $('#budgetAmount').val(card.find('p').first().text().replace(/\D/g, ''));
        $('.modal-title').text('Редактировать бюджет');
        modal.show();
    });

    $('.deleteBtn').on('click', function () {
        if (!confirm('Удалить бюджет?')) return;
        const id = $(this).data('id');
        $.post(`/budget/delete?id=${id}`).done(resp => {
            if (resp.success) location.reload();
            else alert(resp.message);
        });
    });
});
