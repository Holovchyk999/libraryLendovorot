$(document).ready(function() {
    console.log("Enkrypt: Hello from IN");

    $.get('fetch_landings.php', function(data) {
        const groupedByCountry = JSON.parse(data);
        console.log("Коди країн: ", Object.keys(groupedByCountry));
        
        const geoList = $('.geo-list');
        const landingContainer = $('#landing-container');
        const countryHeading = $('#country-heading');

        // Display country codes
        Object.keys(groupedByCountry).forEach(countryCode => {
            geoList.append(`<div class="geo-item" onclick="showLandings('${countryCode}', this)">${countryCode}</div>`);
        });

        // Show landings for selected country
        window.showLandings = function(countryCode, element) {
            console.log("Вибраний код країни: ", countryCode);
            console.log("Лендінги для країни: ", groupedByCountry[countryCode]);
            $('.geo-item').removeClass('active');
            $(element).addClass('active');
            countryHeading.text(countryCode);
            landingContainer.empty();
            groupedByCountry[countryCode].forEach(page => {
                const landingItem = `
                    <li class="landing-item">
                        <a href='${page}/index.html' target='_blank'>${page}</a>
                        <button class="btn btn-primary btn-sm" onclick="openDownloadForm('${page}')">Завантажити</button>
                        <button style="display: none" class="btn btn-secondary btn-sm" onclick="replaceForm('${page}')">Вставити форму</button>
                    </li>`;
                console.log("Додаємо лендінг: ", landingItem);
                landingContainer.append(landingItem);
            });
            $('.landing-list').show();
            landingContainer.show();
        };

        window.openDownloadForm = function(page) {
            $('#landingPage').val(page);
            $('#downloadModal').modal('show');
        };

        window.replaceForm = function(page) {
            $.get(`replace_form.php?page=${page}`, function(response) {
                alert(response);
            });
        };

        $('#downloadForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            const page = $('#landingPage').val();
            window.location.href = `download.php?page=${page}&${formData}`;
        });

        $('#uploadForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const progressBar = $('#uploadProgressBar');
            const progressContainer = $('#uploadProgress');
            const statusText = $('#uploadStatus');

            progressContainer.show();
            progressBar.css('width', '0%').attr('aria-valuenow', 0).text('0%');
            statusText.text('');

            $.ajax({
                url: 'upload.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            const percentComplete = Math.round((e.loaded / e.total) * 100);
                            progressBar.css('width', percentComplete + '%').attr('aria-valuenow', percentComplete).text(percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.status === 'success') {
                        statusText.text('Завантаження пройшло успішно. Перенаправлення на головну сторінку...');
                        setTimeout(function() {
                            window.location.href = 'index.php';
                        }, 2000);
                    } else {
                        statusText.text(data.message);
                    }
                },
                error: function() {
                    statusText.text('Виникла помилка під час завантаження файлу.');
                }
            });
        });
    });
});
