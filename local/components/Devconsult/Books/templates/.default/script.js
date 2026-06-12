BX.namespace('Otus.BookGrid');

BX.Otus.BookGrid = {
    signedParams: null,
    init: function(data) {
        this.signedParams = data.signedParams;
    },
    showMessage: function(message) {
        //alert(message);
    },
    addBook: function() {
        const titleInput = document.getElementById('book-title');
        const authorInput = document.getElementById('book-author');
        const priceInput = document.getElementById('book-price');

        const title = (titleInput?.value || '').trim();
        const author = (authorInput?.value || '').trim();
        const price = parseInt(priceInput?.value || '0', 10);

        BX.ajax.runComponentAction('devconsult:books', 'addBook', {
            mode: 'class',
            signedParameters: BX.Otus.BookGrid.signedParams,
            data: {
                title: title,
                author: author,
                price: Number.isNaN(price) ? 0 : price
            }
        }).then(response => {
            BX.Otus.BookGrid.showMessage(response.data.message + ' (ID: ' + response.data.id + ')');

            if (titleInput) {
                titleInput.value = '';
            }
            if (authorInput) {
                authorInput.value = '';
            }
            if (priceInput) {
                priceInput.value = '0';
            }

            const grid = BX.Main.gridManager.getById('BOOK_GRID')?.instance;
            if (grid) {
                grid.reload();
            }
        }, reject => {
            let errorMessage = '';
            for (const error of reject.errors) {
                errorMessage += error.message + '\n';
            }
            BX.Otus.BookGrid.showMessage(errorMessage || 'Ошибка добавления книги');
        });
    },
    deleteBook(id) {
        BX.ajax.runComponentAction('devconsult:books', 'deleteElement', {
            mode: 'class',
            signedParameters: BX.Otus.BookGrid.signedParams,
            data: {
                bookId: id
            },
        }).then(response => {
            BX.Otus.BookGrid.showMessage('Удалена книга с ID ');
            let grid = BX.Main.gridManager.getById('BOOK_GRID')?.instance;
            grid.reload();
        }, reject => {
            let errorMessage = '';
            for(let error of reject.errors) {
                errorMessage += error.message + '\n';
            }
            BX.Otus.BookGrid.showMessage(errorMessage);
        });
    },
}