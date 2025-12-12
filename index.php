
<?php

$example_persons_array = [
    [
        'fullname' => 'Иванов Иван Иванович',
        'job' => 'tester',
    ],
    [
        'fullname' => 'Степанова Наталья Степановна',
        'job' => 'frontend-developer',
    ],
    [
        'fullname' => 'Пащенко Владимир Александрович',
        'job' => 'analyst',
    ],
    [
        'fullname' => 'Громов Александр Иванович',
        'job' => 'fullstack-developer',
    ],
    [
        'fullname' => 'Славин Семён Сергеевич',
        'job' => 'analyst',
    ],
    [
        'fullname' => 'Цой Владимир Антонович',
        'job' => 'frontend-developer',
    ],
    [
        'fullname' => 'Быстрая Юлия Сергеевна',
        'job' => 'PR-manager',
    ],
    [
        'fullname' => 'Шматко Антонина Сергеевна',
        'job' => 'HR-manager',
    ],
    [
        'fullname' => 'аль-Хорезми Мухаммад ибн-Муса',
        'job' => 'analyst',
    ],
    [
        'fullname' => 'Бардо Жаклин Фёдоровна',
        'job' => 'android-developer',
    ],
    [
        'fullname' => 'Шварцнегер Арнольд Густавович',
        'job' => 'babysitter',
    ],
];



// 1.Разбиение  ФИО
function getPartsFromFullname($fullname) {
    $parts = explode(' ', $fullname);
    return [
        'surname' => $parts[0] ?? '',
        'name' => $parts[1] ?? '',
        'patronymic' => $parts[2] ?? ''
    ];
}



// 2.  Объединение ФИО
function getFullnameFromParts($surname, $name, $patronymic) {
    return $surname . ' ' . $name . ' ' . $patronymic;
}



// 3. Сокращение ФИО
function getShortName($fullname) {
    $parts = getPartsFromFullname($fullname);
    $name = $parts['name'];
    $surnameFirstLetter = mb_substr($parts['surname'], 0, 1, 'UTF-8');
    return $name . ' ' . mb_strtoupper($surnameFirstLetter, 'UTF-8') . '.';
}




// 4. Определение пола по ФИО
function getGenderFromName($fullname) {
    $parts = getPartsFromFullname($fullname);
    $genderScore = 0;
    
    // Мужские признаки
    if (mb_substr($parts['patronymic'], -2, 2, 'UTF-8') === 'ич') $genderScore++;
    if (in_array(mb_substr($parts['name'], -1, 1, 'UTF-8'), ['й', 'н'])) $genderScore++;
    if (mb_substr($parts['surname'], -1, 1, 'UTF-8') === 'в') $genderScore++;
    
    // Женские признаки
    if (mb_substr($parts['patronymic'], -3, 3, 'UTF-8') === 'вна') $genderScore--;
    if (mb_substr($parts['name'], -1, 1, 'UTF-8') === 'а') $genderScore--;
    if (mb_substr($parts['surname'], -2, 2, 'UTF-8') === 'ва') $genderScore--;
    
   if ($genderScore > 0) {
    return 1; // мужской пол
} elseif ($genderScore < 0) {
    return -1; // женский пол
} else {
    return 0; // неопределенный пол
}
}



// 5. Определение полового состава
function getGenderDescription($personsArray) {
    $total = count($personsArray);
    if ($total === 0) return "Аудитория пуста";



     $menCount = count(array_filter($personsArray, function($person) {
        return getGenderFromName($person['fullname']) === 1;
    }));

     $womenCount = count(array_filter($personsArray, function($person) {
        return getGenderFromName($person['fullname']) === -1;
    }));
    
    $unknownCount = count(array_filter($personsArray, function($person) {
        return getGenderFromName($person['fullname']) === 0;
    }));
    
   echo "Гендерный состав аудитории:\n" .
     str_repeat("-", 30) . "\n" .
     "Мужчины - " . round($menCount/$total*100, 1) . "%\n" .
     "Женщины - " . round($womenCount/$total*100, 1) . "%\n" .
     "Не удалось определить - " . round($unknownCount/$total*100, 1) . "%";
    

    }






// 6. Идеальный подбор пары

function getPerfectPartner($surname, $name, $patronymic, $personsArray) {
    
    if (!function_exists('normalFio')) {
        function normalFio($surname, $name, $patronymic) {
            return [
                mb_convert_case(trim($surname), MB_CASE_TITLE, 'UTF-8'),
                mb_convert_case(trim($name), MB_CASE_TITLE, 'UTF-8'),
                mb_convert_case(trim($patronymic), MB_CASE_TITLE, 'UTF-8')
            ];
        }
    }
    

     
        list($surname, $name, $patronymic) = normalFio($surname, $name, $patronymic);
    $fullname = getFullnameFromParts($surname, $name, $patronymic);
    $userGender = getGenderFromName($fullname);
    
    
    if ($userGender === 0) return "Не удалось определить ваш пол";
    
    
    $attempts = 0;
    do {
        $partner = $personsArray[array_rand($personsArray)];
        $partnerGender = getGenderFromName($partner['fullname']);
        $attempts++;
    } while (($partnerGender === 0 || $partnerGender === $userGender) && $attempts < 100);
    
    if ($attempts >= 100) return "Нет подходящих кандидатов";
    
    $compatibility = mt_rand(5000, 10000) / 100;
    
    return getShortName($fullname) . " + " . getShortName($partner['fullname']) . " =\n" .
           "♡ Идеально на " . number_format($compatibility, 2) . "% ♡";
}



// ПРОВЕРКА РАБОТЫ
//----------------------------------------------------

echo '<div class="test-box" style="border-color: #97cae4ff;">
        <h3 style="color: #87b3c9ff; margin-top: 0;"> Проверка работы на массиве</h3>
        <pre>';
foreach ($example_persons_array as $index => $person) {
    $person_parts = getPartsFromFullname($person['fullname']);
    $person_gender = getGenderFromName($person['fullname']);
    $person_gender_text = ($person_gender === 1) ? 'М' : (($person_gender === -1) ? 'Ж' : '? Н/О');
    
    echo sprintf("%2d. ФИО: %-35s → Сокращенно: %-15s Пол: %s (%d)\n", 
        $index + 1, 
        $person['fullname'], 
        getShortName($person['fullname']), 
        $person_gender_text,
        $person_gender
    );
}
echo '</pre>
      </div>';




//1. Разделение имени

$random_index = array_rand($example_persons_array);
$random_person = $example_persons_array[$random_index];
$random_fullname = $random_person['fullname'];

echo '<div style="margin: 20px 0; padding: 15px; border: 2px solid #dd9255ff; border-radius: 5px; background: #f9f9f9;">';
echo '<h3 style="color: #8a7329ff;"> 📝1. Разделение ФИО (getPartsFromFullname) </h3>';
echo '<p><strong>Начальные данные:</strong> ' . $random_fullname . '"</p>';
echo '<pre>' . print_r(getPartsFromFullname($random_fullname), true) . '</pre>';
echo '</div>';







//2. Объединение ФИО

$parts = getPartsFromFullname($random_fullname);
$random_surname = $parts['surname'];
$random_name = $parts['name'];
$random_patronymic = $parts['patronymic'];

echo '<div style="margin: 20px 0; padding: 15px; border: 2px solid #399b5aff; border-radius: 5px; background: #f9f9f9;">';
echo '<h3 style="color: #4fa84cff;"> 📝2. Склеивание ФИО (getFullnameFromParts) </h3>';
echo '<p><strong>Начальные данные:</strong><br>';
echo        'Фамилия: "' . $random_surname . '"<br>';
echo        'Имя: "' . $random_name . '"<br>';
echo        'Отчество: "' . $random_patronymic . '"</p>';
echo       '<p><strong>Результат:</strong></p>';
echo '<pre>' . getFullnameFromParts($random_surname, $random_name, $random_patronymic) . '</pre>';
echo '</div>';




//3. Сокращение имени по ФИО

echo '<div style="margin: 20px 0; padding: 15px; border: 2px solid #d37049ff; border-radius: 5px; background: #f9f9f9;">';
echo '<h3 style="color: #dd6f44ff;">📝3. Сокращение ФИО (getShortName)</h3>';
echo '<p><strong>Начальные данные:</strong> ' . $random_fullname . '"</p>';
echo '<pre>' . getShortName($random_fullname) . '</pre>';
echo '</div>';




//4: Определение пола
$gender_result = getGenderFromName($random_fullname);
$gender_text = ($gender_result === 1) ? 'Мужской пол' : (($gender_result === -1) ? 'Женский пол' : 'Неопределенный пол');
echo '<div style="margin: 20px 0; padding: 15px; border: 2px solid #2196F3; border-radius: 5px; background: #f9f9f9;">';
echo '<h3 style="color: #2196F3;">📝4. Определение пола (getGenderFromName)</h3>';
echo '<p><strong>Начальные данные:</strong> "' . $random_fullname . '"</p>';
echo '<p><strong>Результат:</strong></p>';
echo '<pre>Код пола: ' . $gender_result . ' (' . $gender_text . ')</pre>';
echo '</div>';




//5. Гендерный состав всей аудитории

echo '<div style="margin: 20px 0; padding: 15px; border: 2px solid #255829ff; border-radius: 5px; background: #f9f9f9;">';
echo '<h3 style="color: #25502cff;">📝5. Гендерный анализ аудитории (getGenderDescription)</h3>';
echo '<p><strong>Входные данные:</strong> весь массив (' . count($example_persons_array) . ' человек)</p>';
echo '<p><strong>Результат:</strong></p>';
echo '<pre>' . getGenderDescription($example_persons_array) . '</pre>';
echo '</div>';





// 6. Подбор идеальной пары

echo '<div style="margin: 20px 0; padding: 15px; border: 2px solid #da3030ff; border-radius: 5px; background: #f9f9f9;">';
echo '<h3 style="color: #dd2929ff;">💖6. Подбор идеальной пары (getPerfectPartner)</h3>';
echo '<p><strong>Начальные данные:</strong><br>';
echo        'Фамилия: "' . $random_surname . '"<br>';
echo        'Имя: "' . $random_name . '"<br>';
echo        'Отчество: "' . $random_patronymic . '"</p>';
echo       '<p><strong>Результат:</strong></p>';
echo ' <pre>' . getPerfectPartner($random_surname, $random_name, $random_patronymic, $example_persons_array) . '</pre>';
echo '</div>';


?>