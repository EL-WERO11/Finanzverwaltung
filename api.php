<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Debug-Logging aktivieren
file_put_contents('api_debug.log', date('Y-m-d H:i:s')." - Request: ".print_r($_REQUEST, true)."\n", FILE_APPEND);
file_put_contents('api_debug.log', date('Y-m-d H:i:s')." - Input: ".file_get_contents('php://input')."\n", FILE_APPEND);

require 'db_connect.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function checkAdminAuth() {
    if (!isset($_SESSION['admin_logged_in'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized', 'code' => 401]);
        exit;
    }
}

function calculateNextDate($startDate, $frequency) {
    try {
        $date = new DateTime($startDate);
        switch ($frequency) {
            case 'weekly': $date->modify('+1 week'); break;
            case 'biweekly': $date->modify('+2 weeks'); break;
            case 'monthly': $date->modify('+1 month'); break;
            case 'quarterly': $date->modify('+3 months'); break;
            case 'yearly': $date->modify('+1 year'); break;
            default: throw new Exception('Ungültige Frequenz');
        }
        return $date->format('Y-m-d');
    } catch (Exception $e) {
        throw new Exception('Ungültiges Datum: ' . $e->getMessage());
    }
}

function processExpiredInstallments($pdo) {
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT id FROM installment_payments WHERE end_date < ?");
    $stmt->execute([$today]);
    $expiredIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!empty($expiredIds)) {
        $placeholders = implode(',', array_fill(0, count($expiredIds), '?'));
        $stmt = $pdo->prepare("DELETE FROM installment_payments WHERE id IN ($placeholders)");
        $stmt->execute($expiredIds);
    }
}

try {
    processExpiredInstallments($pdo);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        checkAdminAuth();
        
        if (!isset($_GET['action'])) {
            throw new Exception('Action parameter fehlt', 400);
        }

        if ($_GET['action'] === 'get_data') {
            $response = ['status' => 'success'];
            
            $stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
            $response['settings'] = $stmt->fetch() ?: [
                'monthly_budget' => 0,
                'savings_percentage' => 10,
                'current_month' => date('Y-m')
            ];

            try {
                $response['incomes'] = $pdo->query("SELECT id, description, CAST(amount AS DECIMAL(10,2)) as amount, frequency, next_date FROM incomes")->fetchAll();
                $response['expenses'] = $pdo->query("SELECT id, description, CAST(amount AS DECIMAL(10,2)) as amount, category, date FROM expenses")->fetchAll();
                $response['recurringExpenses'] = $pdo->query("SELECT id, description, CAST(amount AS DECIMAL(10,2)) as amount, category, frequency, start_date, next_date FROM recurring_expenses")->fetchAll();
                $response['installments'] = $pdo->query("SELECT id, description, CAST(total_amount AS DECIMAL(10,2)) as total_amount, CAST(installment_amount AS DECIMAL(10,2)) as installment_amount, start_date, end_date, frequency, next_payment_date, CAST(remaining_amount AS DECIMAL(10,2)) as remaining_amount FROM installment_payments")->fetchAll();
                $response['categories'] = $pdo->query("SELECT id, name, color, is_default FROM categories")->fetchAll();
                
			// Füge stattdessen diesen dynamischen Berechnungscode ein:
			$response['monthlyData'] = [];

			// Einnahmen (nur fällige in jedem Monat)
			$incomes = $pdo->query("
				SELECT 
					DATE_FORMAT(next_date, '%Y-%m') as month,
					SUM(amount) as income
				FROM incomes
				WHERE next_date IS NOT NULL
				GROUP BY DATE_FORMAT(next_date, '%Y-%m')
			")->fetchAll(PDO::FETCH_UNIQUE);

			// Ausgaben (einmalige + wiederkehrende + Raten)
			$expenses = $pdo->query("
				SELECT month, SUM(amount) as expenses FROM (
					SELECT DATE_FORMAT(date, '%Y-%m') as month, amount FROM expenses
					UNION ALL
					SELECT DATE_FORMAT(next_date, '%Y-%m') as month, amount FROM recurring_expenses
					UNION ALL
					SELECT DATE_FORMAT(next_payment_date, '%Y-%m') as month, installment_amount as amount FROM installment_payments
				) as combined
				GROUP BY month
			")->fetchAll(PDO::FETCH_UNIQUE);

			// Kombiniere die Daten
			foreach (array_keys(array_merge($incomes, $expenses)) as $month) {
				$response['monthlyData'][$month] = [
					'income' => (float)($incomes[$month]['income'] ?? 0),
					'expenses' => (float)($expenses[$month]['expenses'] ?? 0)
				];
			}
			
                
            } catch (PDOException $e) {
                throw new Exception('Datenbankfehler: ' . $e->getMessage());
            }

            echo json_encode($response);
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        checkAdminAuth();
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Ungültiges JSON-Format', 400);
        }
        
        if (!isset($_GET['action'])) {
            throw new Exception('Action parameter fehlt', 400);
        }

switch ($_GET['action']) {
    case 'add_income':
        $required = ['description', 'amount', 'frequency'];
        if (count(array_diff($required, array_keys($input))) > 0) {
            throw new Exception('Erforderliche Felder fehlen: ' . implode(', ', $required), 400);
        }
        
        $stmt = $pdo->prepare("INSERT INTO incomes (description, amount, frequency, next_date) VALUES (?, ?, ?, ?)");
        $success = $stmt->execute([
            htmlspecialchars($input['description']),
            (float)$input['amount'],
            $input['frequency'],
            $input['nextDate'] ?? date('Y-m-d')
        ]);
        
        if (!$success) {
            throw new Exception('Einnahme konnte nicht gespeichert werden');
        }
        break;
                
    case 'add_expense':
        $required = ['description', 'amount', 'category', 'date'];
        if (count(array_diff($required, array_keys($input))) > 0) {
            throw new Exception('Erforderliche Felder fehlen: ' . implode(', ', $required), 400);
        }
        
        $stmt = $pdo->prepare("INSERT INTO expenses (description, amount, category, date) VALUES (?, ?, ?, ?)");
        $success = $stmt->execute([
            htmlspecialchars($input['description']),
            (float)$input['amount'],
            $input['category'],
            $input['date']
        ]);
        
        if (!$success) {
            throw new Exception('Ausgabe konnte nicht gespeichert werden');
        }
        break;
                
    case 'add_recurring':
        $required = ['description', 'amount', 'category', 'frequency', 'startDate'];
        if (count(array_diff($required, array_keys($input))) > 0) {
            throw new Exception('Erforderliche Felder fehlen: ' . implode(', ', $required), 400);
        }
        
        $nextDate = calculateNextDate($input['startDate'], $input['frequency']);
        
        $stmt = $pdo->prepare("INSERT INTO recurring_expenses (description, amount, category, frequency, start_date, next_date) VALUES (?, ?, ?, ?, ?, ?)");
        $success = $stmt->execute([
            htmlspecialchars($input['description']),
            (float)$input['amount'],
            $input['category'],
            $input['frequency'],
            $input['startDate'],
            $nextDate
        ]);
        
        if (!$success) {
            throw new Exception('Wiederkehrende Ausgabe konnte nicht gespeichert werden');
        }
        break;
                
    case 'add_installment':
        $required = ['description', 'totalAmount', 'installmentAmount', 'startDate', 'endDate', 'frequency'];
        if (count(array_diff($required, array_keys($input))) > 0) {
            throw new Exception('Erforderliche Felder fehlen: ' . implode(', ', $required), 400);
        }
        
        $nextDate = calculateNextDate($input['startDate'], $input['frequency']);
        $remaining = (float)$input['totalAmount'] - (float)$input['installmentAmount'];
        
        $stmt = $pdo->prepare("INSERT INTO installment_payments 
            (description, total_amount, installment_amount, start_date, end_date, frequency, next_payment_date, remaining_amount) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        $success = $stmt->execute([
            htmlspecialchars($input['description']),
            (float)$input['totalAmount'],
            (float)$input['installmentAmount'],
            $input['startDate'],
            $input['endDate'],
            $input['frequency'],
            $nextDate,
            $remaining
        ]);
        
        if (!$success) {
            throw new Exception('Ratenzahlung konnte nicht gespeichert werden');
        }
        break;
                
    case 'save_settings':
        $required = ['monthlyBudget', 'savingsPercentage', 'currentMonth'];
        if (count(array_diff($required, array_keys($input))) > 0) {
            throw new Exception('Erforderliche Felder fehlen: ' . implode(', ', $required), 400);
        }
        
        $savingsPercentage = (int)$input['savingsPercentage'];
        if ($savingsPercentage < 0 || $savingsPercentage > 100) {
            throw new Exception('Sparrate muss zwischen 0 und 100 liegen', 400);
        }
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM settings");
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $stmt = $pdo->prepare("UPDATE settings SET monthly_budget = ?, savings_percentage = ?, current_month = ?");
        } else {
            $stmt = $pdo->prepare("INSERT INTO settings (monthly_budget, savings_percentage, current_month) VALUES (?, ?, ?)");
        }
        
        $success = $stmt->execute([
            (float)$input['monthlyBudget'],
            $savingsPercentage,
            $input['currentMonth']
        ]);
        
        if (!$success) {
            throw new Exception('Einstellungen konnten nicht gespeichert werden');
        }
        break;
                
            case 'add_category':
                $required = ['name', 'color'];
                if (count(array_diff($required, array_keys($input))) > 0) {
                    throw new Exception('Erforderliche Felder fehlen: ' . implode(', ', $required), 400);
                }
                
                $stmt = $pdo->prepare("INSERT INTO categories (name, color) VALUES (?, ?)");
                $success = $stmt->execute([
                    htmlspecialchars($input['name']),
                    $input['color']
                ]);
                
                if (!$success) {
                    throw new Exception('Kategorie konnte nicht hinzugefügt werden');
                }
                break;
                
			case 'delete_category':
				if (!isset($input['id'])) {
					throw new Exception('ID fehlt', 400);
				}

				$stmt = $pdo->prepare("SELECT is_default FROM categories WHERE id = ?");
				$stmt->execute([$input['id']]);
				$category = $stmt->fetch();

				if ($category && $category['is_default']) {
					throw new Exception('Standardkategorien können nicht gelöscht werden', 400);
				}

				$stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
				$success = $stmt->execute([$input['id']]);

				if (!$success) {
					throw new Exception('Kategorie konnte nicht gelöscht werden');
				}
				break;
                
            default:
                throw new Exception('Ungültige Aktion', 400);
        }
        
        echo json_encode(['status' => 'success']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        checkAdminAuth();
        
        if (!isset($_GET['type'], $_GET['id'])) {
            throw new Exception('Fehlende Parameter', 400);
        }
        
        $id = (int)$_GET['id'];
        if ($id <= 0) {
            throw new Exception('Ungültige ID', 400);
        }
        
        $validTypes = ['income', 'expense', 'recurring', 'installment', 'category'];
        $type = $_GET['type'];
        
        if (!in_array($type, $validTypes)) {
            throw new Exception('Ungültiger Typ', 400);
        }
        
        $tableMap = [
            'income' => 'incomes',
            'expense' => 'expenses',
            'recurring' => 'recurring_expenses',
            'installment' => 'installment_payments',
            'category' => 'categories'
        ];
        
        $table = $tableMap[$type];
        
        // Zusätzliche Überprüfung für Kategorien
        if ($type === 'category') {
            $stmt = $pdo->prepare("SELECT is_default FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $category = $stmt->fetch();
            
            if ($category && $category['is_default']) {
                throw new Exception('Standardkategorien können nicht gelöscht werden', 400);
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
        $success = $stmt->execute([$id]);
        
        if (!$success) {
            throw new Exception('Eintrag konnte nicht gelöscht werden');
        }
        
        echo json_encode(['status' => 'success']);
        exit;
    }
    
    throw new Exception('Methode nicht erlaubt', 405);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'status' => 'error',
        'error' => $e->getMessage(),
        'code' => $e->getCode() ?: 500
    ]);
    exit;
}
?>