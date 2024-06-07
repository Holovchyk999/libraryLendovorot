<?php

if (!isset($_GET['page'])) {
    die("No page specified.");
}

$page = $_GET['page'];
$directory = __DIR__ . '/' . $page;

if (!is_dir($directory)) {
    die("Invalid page specified.");
}

$gi = isset($_GET['gi']) ? $_GET['gi'] : '';
$mpc_2 = isset($_GET['mpc_2']) ? $_GET['mpc_2'] : '';
$lang_code = isset($_GET['lang_code']) ? $_GET['lang_code'] : 'en';

// Динамічне визначення phone_code
$phone_code = isset($_GET['phone_code']) ? $_GET['phone_code'] : 'gb';
if ($phone_code === 'true') {
    $phone_code = 'auto';
}

$min_digits = isset($_GET['min_digits']) ? $_GET['min_digits'] : 9;
$max_digits = isset($_GET['max_digits']) ? $_GET['max_digits'] : 15;

$scriptFormContent = <<<JS
$(document).ready(function() {
    $.validator.addMethod("lettersOnly", function(value, element) {
        return this.optional(element) || /^[a-zA-Zа-яА-ЯёЁіІїЇєЄ']+$/i.test(value);
    }, "Ім'я може містити тільки літери");

    function setLanguage(lang) {
        console.log("Selected language:", lang);
        const messages = translations[lang];
        if (!messages) {
            console.error("No translations found for the selected language:", lang);
            return;
        }

        if ($.validator && $("#dynamicForm").data('validator')) {
            $("#dynamicForm").validate().settings.messages = {
                first_name: messages.firstName,
                last_name: messages.lastName,
                email: messages.email,
                phone: messages.phone
            };
        }
    }

    function setupValidation() {
        if ($("#dynamicForm").length) {
            $("#dynamicForm").validate({
                rules: {
                first_name: {
                    required: true,
                    minlength: 2,
                    lettersOnly: true
                },
                last_name: {
                    required: true,
                    minlength: 2,
                    lettersOnly: true
                },
                email: {
                    required: true,
                    email: true
                },
                phone: {
                    required: true,
                    digits: true,
                    minlength: $min_digits,
                    maxlength: $max_digits
                },
                ageCheck: {
                    required: true
                },
                callCheck: {
                    required: true
                }
            },
messages: {
    first_name: {
        required: "Please enter your first name",
        minlength: "First name must be at least 2 characters long",
        lettersOnly: "First name can only contain letters"
    },
    last_name: {
        required: "Please enter your last name",
        minlength: "Last name must be at least 2 characters long",
        lettersOnly: "Last name can only contain letters"
    },
    email: {
        required: "Please enter your email address",
        email: "Please enter a valid email address"
    },
    phone: {
        required: "Please enter your phone number",
        digits: "Please enter only numbers",
                    minlength: "Phone number must contain at least $min_digits digits",
                    maxlength: "Phone number can contain at most $max_digits digits"
                },
    ageCheck: {
        required: "You must be over 18 years old"
    },
    callCheck: {
        required: "You must agree to receive a call from the manager within 24 hours"
    }
            },
                errorPlacement: function(error, element) {
                    if (element.attr("type") === "checkbox") {
                        error.insertAfter(element.closest('.form-check'));
                    } else {
                        error.insertAfter(element);
                    }
                },
                highlight: function(element, errorClass, validClass) {
                    $(element).addClass('is-invalid').removeClass('is-valid');
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).removeClass('is-invalid').addClass('is-valid');
                },
                success: function(label, element) {
                    $(element).removeClass('is-invalid').addClass('is-valid');
                    if ($(element).attr("type") === "checkbox") {
                        $(element).closest('.form-check').find('label.error').remove();
                    }
                },
                submitHandler: function(form) {
                    console.log('Form is valid, preparing to submit...');
                    updateDialCodeAndCountry();
                    showLoader();
                    console.log('Submitting form...');
                    
                    var formData = $(form).serialize();
                    
                    $.ajax({
                        url: form.action,
                        type: form.method,
                        data: formData,
                        dataType: 'json',
                        success: function(response) {
                            hideLoader();
                            if (response.status === 'true') {
                                window.location.href = './thanks/?pixel=' + response.pixel;
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Ops!',
                                    text: 'Sorry, technical work is in progress'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            hideLoader();
                            Swal.fire({
                                icon: 'error',
                                title: 'Ops!',
                                text: 'Sorry, technical work is in progress'
                            });
                        }
                    });
                }
            });

            $("input[name='phone']").on('input', function() {
                var value = $(this).val();
                var cleanValue = value.replace(/\D/g, '');
                $(this).val(cleanValue);
            });

            function showLoader() {
                $('#loader').removeClass('d-none');
            }

            function hideLoader() {
                $('#loader').addClass('d-none');
            }

            var telCode = document.getElementsByClassName("phone");
            var itiInstances = [];
             jQuery.each(telCode, function(index, value) {
                var instance = window.intlTelInput(value, {
                    allowDropdown: false,
                    autoHideDialCode: true,
                    autoPlaceholder: "polite",
                    formatOnDisplay: true,
                    geoIpLookup: function(callback) {
                        $.get("https://get.geojs.io/v1/ip/geo.js", function() {}, "jsonp").always(function(resp) {
                            var countryCode = (resp && resp.country_code) ? resp.country_code.toLowerCase() : "en";
                            callback(countryCode);
                        });
                    },
                initialCountry: "$phone_code",
                   nationalMode: true,
                    placeholderNumberType: "MOBILE",
                    separateDialCode: true,
                    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
                });
                itiInstances.push(instance);
                value.addEventListener("countrychange", function() {
                    if (typeof instance.getSelectedCountryData === 'function') {
                        const selectedCountryData = instance.getSelectedCountryData();
                        const dialCode = selectedCountryData.dialCode;
                        if (dialCode) {
                            $("input[name='dialCode']").val(dialCode);
                        }
                    }
                });
            });

            function updateDialCodeAndCountry() {
                $(".phone").each(function(index, element) {
                    const itiInstance = itiInstances[index];
                    if (itiInstance && typeof itiInstance.getSelectedCountryData === 'function') {
                        const selectedCountryData = itiInstance.getSelectedCountryData();
                        const dialCode = selectedCountryData.dialCode;
                        const countryCode = selectedCountryData.iso2;
                        if (dialCode) {
                            $("input[name='dialCode']").val(dialCode);
                        }
                        if (countryCode) {
                            $("input[name='country']").val(countryCode);
                        }
                    }
                });
            }
        setLanguage('$lang_code');
        } else {
            console.error("Form with id 'dynamicForm' not found.");
        }
    }

    setupValidation();
});
JS;

$api_php_content = <<<PHP
<?php
header('Content-Type: application/json');

// get cURL resource
\$ch = curl_init();

// set url
curl_setopt(\$ch, CURLOPT_URL, 'https://tb.tmcteam.pro/api/signup/procform');

// set method
curl_setopt(\$ch, CURLOPT_CUSTOMREQUEST, 'POST');

// return the transfer as a string
curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, 1);
\$phone = stripslashes(htmlspecialchars(\$_POST['phone']));
\$country = stripslashes(htmlspecialchars(\$_POST['country']));
\$dialCode = stripslashes(htmlspecialchars(\$_POST['dialCode']));
\$subid = stripslashes(htmlspecialchars(\$_POST['subid']));

// set headers
curl_setopt(\$ch, CURLOPT_HTTPHEADER, [
  'x-trackbox-username: keitarobuying',
  'x-trackbox-password: jjv89rj894jvj94JW974@\$',
  'x-api-key: 2643889w34df345676ssdas323tgc738',
  'Content-Type: application/json',
]);

// json body
\$json_array = [
  'ai' => '2958036',
  'ci' => '1',
  'gi' => '$gi',
  'userip' => \$_SERVER['REMOTE_ADDR'],
  'country' => \$country,
  'firstname' => \$_POST['first_name'],
  'lastname' => \$_POST['last_name'],
  'email' => \$_POST['email'],
  'password' => 'Qbwriu46',
  'phone' => preg_replace('/[^0-9]/', '', \$dialCode . \$phone),
  'so' => '$page',
  'MPC_1' => \$_POST['subid'],
  'MPC_2' => '$mpc_2',
]; 
\$body = json_encode(\$json_array);

// set body
curl_setopt(\$ch, CURLOPT_POST, 1);
curl_setopt(\$ch, CURLOPT_POSTFIELDS, \$body);

// send the request and save response to \$response
\$response = curl_exec(\$ch);

// Check for cURL errors
if(curl_errno(\$ch)) {
    echo json_encode(['status' => 'false', 'message' => 'cURL Error: '.curl_error(\$ch)]);
    exit;
}

// close curl resource to free up system resources 
curl_close(\$ch);

extract(\$_REQUEST);
\$file=fopen("form-save.txt","a");

fwrite(\$file,"Новая заявка\n");
fwrite(\$file,"Дата/время: ");
fwrite(\$file, date('d.m.Y H:i', strtotime('+3 hours')) ."\n");
fwrite(\$file,"ID клика: ");
fwrite(\$file, \$_POST['subid'] ."\n");
fwrite(\$file,"Имя: ");
fwrite(\$file, \$_POST['first_name'] ."\n");
fwrite(\$file,"Фамилия: ");
fwrite(\$file, \$_POST['last_name'] ."\n");
fwrite(\$file,"Почта: ");
fwrite(\$file, \$_POST['email'] ."\n");
fwrite(\$file,"Телефон: ");
fwrite(\$file, \$dialCode . \$phone ."\n");
fwrite(\$file,"Ответ сервера: ");
fwrite(\$file, \$response ."\n\n");
fclose(\$file);

\$arr = json_decode(\$response, true);

if (\$arr['status'] == 'true') {
  setcookie('cabinet', \$arr['data'], time()+60*90);
  echo json_encode(['status' => 'true', 'pixel' => \$_POST['pixel']]);
} else {
  echo json_encode(['status' => 'false', 'message' => \$arr['message']]);
}
?>
PHP;

$zip = new ZipArchive();
$zipFileName = $page . ".zip";

if ($zip->open($zipFileName, ZipArchive::CREATE) !== TRUE) {
    die("Could not create ZIP file.");
}

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory), RecursiveIteratorIterator::LEAVES_ONLY);

foreach ($files as $file) {
    if (!$file->isDir()) {
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($directory) + 1);
        $zip->addFile($filePath, $relativePath);
    }
}

// Add the api.php file to the root of the landing page
$zip->addFromString('api.php', $api_php_content);

// Add the updated scriptForm.js to the ForForm/js/ directory
$scriptFormPath = "$directory/ForForm/js/scriptForm.js";
if (file_exists($scriptFormPath)) {
    unlink($scriptFormPath); // Remove the existing file
}
$zip->addFromString( '/ForForm/js/scriptForm.js', $scriptFormContent);

$zip->close();

header('Content-Type: application/zip');
header('Content-disposition: attachment; filename=' . $zipFileName);
header('Content-Length: ' . filesize($zipFileName));
readfile($zipFileName);

unlink($zipFileName);

exit();
?>
