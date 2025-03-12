<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $domain = $_POST['domain'];
    $memo = preg_split('/\r\n|\r|\n/', trim($_POST['memo']));
    $api_key = "";
    $user_id = "";
    $results = [];

    foreach ($memo as $keyword) {
        $query = urlencode(trim($keyword));
        $url = "https://xmlriver.com/search/xml?query=$query&key=$api_key&user=$user_id";
        $response = file_get_contents($url);

        if ($response === FALSE) {
            $results[] = [
                'keyword' => $keyword,
                'position' => 'Ошибка запроса'
            ];
            continue;
        }

        $xml = simplexml_load_string($response);
        if ($xml === FALSE) {
            $results[] = [
                'keyword' => $keyword,
                'position' => 'Ошибка обработки XML'
            ];
            continue;
        }

        $found = false;
        foreach ($xml->response->results->grouping->group as $group) {
            $url = (string) $group->doc->url;
            if (strpos($url, $domain) !== false) {
                $results[] = [
                    'keyword' => $keyword,
                    'position' => (string) $group['id']
                ];
                $found = true;
                break;
            }
        }

        if (!$found) {
            $results[] = [
                'keyword' => $keyword,
                'position' => 'Сайт не найден'
            ];
        }
    }

    // Возвращаем результаты в формате JSON
    header('Content-Type: application/json');
    echo json_encode($results);
    exit();
}
?>
