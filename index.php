<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Persönliche Finanzverwaltung</title>
    <link rel="stylesheet" href="styles.css">
    <!-- Font Awesome für Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .tab-content:not(.active) {
            display: none;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .dark-toggle-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            z-index: 1000;
            box-shadow: var(--shadow-md);
        }

        .dark-toggle-btn:hover {
            background: var(--primary-dark);
        }
        
        .alert {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 12px 24px;
            border-radius: 8px;
            color: white;
            z-index: 1001;
            animation: fadeIn 0.3s;
        }
        .alert-error { background-color: #f44336; }
        .alert-success { background-color: #4CAF50; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        
        .spinner {
            color: var(--primary);
            font-size: 3rem;
        }
        
        .dark-mode {
            --background: #1e1e2f;
            --text: #f1f1f1;
            --card-bg: #2c2f48;
            --gray-light: #3b3f5a;
            --primary: #7aa2f7;
            --primary-dark: #6a90f0;
            --primary-light: #a3c0ff;
        }
        
        .summary-item.income {
            background-color: rgba(76, 175, 80, 0.1);
            border-left: 4px solid #4CAF50;
        }
        
        .summary-item.expense {
            background-color: rgba(244, 67, 54, 0.1);
            border-left: 4px solid #F44336;
        }
        
        .summary-item.upcoming {
            background-color: rgba(255, 235, 59, 0.1);
            border-left: 4px solid #FFEB3B;
        }
        
        .summary-item.savings {
            background-color: rgba(33, 150, 243, 0.1);
            border-left: 4px solid #2196F3;
        }
        
        .summary-item.balance-positive {
            background-color: rgba(76, 175, 80, 0.2);
            border-left: 4px solid #4CAF50;
        }
        
        .summary-item.balance-negative {
            background-color: rgba(244, 67, 54, 0.2);
            border-left: 4px solid #F44336;
        }
        
        .positive {
            color: #4CAF50;
            font-weight: bold;
        }
        
        .negative {
            color: #F44336;
            font-weight: bold;
        }
        
        .upcoming-amount {
            color: #FF9800;
            font-weight: bold;
        }
        
        .due-today {
            background-color: rgba(244, 67, 54, 0.1) !important;
        }
        
        .month-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            padding: 8px 12px;
            margin-bottom: 4px;
            border-radius: 4px;
        }
        
        .month-details {
            margin-top: 12px;
        }
        
        /* Neue Icon-Styles */
        .icon-lg {
            font-size: 1.5em;
            margin-right: 8px;
        }
        
        .icon-md {
            font-size: 1.2em;
            margin-right: 6px;
        }
        
        .icon-sm {
            font-size: 1em;
            margin-right: 4px;
        }
        
        .badge i {
            margin-right: 4px;
        }
        
        /* Verbesserte Tab-Icons */
        .tab i {
            transition: all 0.2s ease;
        }
        
        .tab.active i {
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <!-- Dark Mode Toggle Button -->
    <button id="darkToggle" class="dark-toggle-btn">
        <i class="fas fa-moon"></i> Dunkel
    </button>

    <div class="container">
        <div class="mobile-header">
            <button class="mobile-menu-btn" id="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <div class="page-header">
            <h1><i class="fas fa-wallet icon-lg"></i> Persönliche Finanzverwaltung</h1>
        </div>
        
        <div class="tabs-container">
            <div class="tabs" id="tabs">
                <div class="tab active" data-tab="overview">
                    <i class="fas fa-chart-pie"></i>
                    <span class="tab-text">Übersicht</span>
                </div>
                <div class="tab" data-tab="income">
                    <i class="fas fa-money-bill-trend-up"></i>
                    <span class="tab-text">Einnahmen</span>
                </div>
                <div class="tab" data-tab="expenses">
                    <i class="fas fa-money-bill-transfer"></i>
                    <span class="tab-text">Ausgaben</span>
                </div>
                <div class="tab" data-tab="recurring">
                    <i class="fas fa-arrows-rotate"></i>
                    <span class="tab-text">Wiederkehrend</span>
                </div>
                <div class="tab" data-tab="installments">
                    <i class="fas fa-calendar-days"></i>
                    <span class="tab-text">Raten</span>
                </div>
                <div class="tab" data-tab="settings">
                    <i class="fas fa-gear"></i>
                    <span class="tab-text">Einstellungen</span>
                </div>
            </div>
        </div>
        
        <!-- Übersicht Tab -->
        <div id="overview" class="tab-content active card">
            <h2><i class="fas fa-chart-pie icon-md"></i> Monatsübersicht</h2>
            <div class="summary">
                <div class="summary-item income">
                    <h3><i class="fas fa-money-bill-wave icon-md"></i> Einnahmen diesen Monat</h3>
                    <p id="total-income">0 €</p>
                </div>
                <div class="summary-item expense">
                    <h3><i class="fas fa-receipt icon-md"></i> Ausgaben diesen Monat</h3>
                    <p id="total-expenses">0 €</p>
                </div>
                <div class="summary-item">
                    <h3><i class="fas fa-coins icon-md"></i> Verfügbares Budget</h3>
                    <p id="available-budget">0 €</p>
                </div>
                <div class="summary-item">
                    <h3><i class="fas fa-calendar-arrow-down icon-md"></i> Übertrag nächster Monat</h3>
                    <p id="next-month-transfer">0 €</p>
                </div>
                <div class="summary-item warning">
                    <h3><i class="fas fa-calendar-times icon-md"></i> Ausgaben Ende des Monats</h3>
                    <p>${formatCurrency(totalCurrentMonthExpenses)}</p>
                </div>
            </div>
            <h3></h3>
			<h3 style="text-align: center;">
				<i class="fas fa-calendar-day icon-md"></i> Kommende Ausgaben
			</h3>

			
            <div class="expenses-section">
                <h4><i class="fas fa-calendar-week icon-sm"></i> Dieser Monat (<span id="current-month-name"></span>)</h4>
                <div style="overflow-x: auto;">
                    <table id="current-month-expenses">
                        <thead>
                            <tr>
                                <th><i class="fas fa-tag"></i> Bezeichnung</th>
                                <th><i class="fas fa-euro-sign"></i> Betrag</th>
                                <th><i class="fas fa-folder"></i> Kategorie</th>
                                <th><i class="fas fa-calendar-day"></i> Fällig am</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            
            <div class="expenses-section" style="margin-top: 20px;">
                <h4><i class="fas fa-calendar-week icon-sm"></i> Nächster Monat (<span id="next-month-name"></span>)</h4>
                <div style="overflow-x: auto;">
                    <table id="next-month-expenses">
                        <thead>
                            <tr>
                                <th><i class="fas fa-tag"></i> Bezeichnung</th>
                                <th><i class="fas fa-euro-sign"></i> Betrag</th>
                                <th><i class="fas fa-folder"></i> Kategorie</th>
                                <th><i class="fas fa-calendar-day"></i> Fällig am</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Einnahmen Tab -->
        <div id="income" class="tab-content card">
            <h2><i class="fas fa-money-bill-trend-up icon-md"></i> Einnahmen verwalten</h2>
            
            <div class="form-group">
                <label for="income-description"><i class="fas fa-tag icon-sm"></i> Bezeichnung</label>
                <input type="text" id="income-description" placeholder="z.B. Gehalt">
            </div>
            
            <div class="form-group">
                <label for="income-amount"><i class="fas fa-euro-sign icon-sm"></i> Betrag (€)</label>
                <input type="number" id="income-amount" step="0.01" placeholder="z.B. 2500.00">
            </div>
            
            <div class="form-group">
                <label for="income-frequency"><i class="fas fa-sync-alt icon-sm"></i> Häufigkeit</label>
                <select id="income-frequency">
                    <option value="monthly">Monatlich</option>
                    <option value="biweekly">Alle 2 Wochen</option>
                    <option value="weekly">Wöchentlich</option>
                    <option value="quarterly">Vierteljährlich</option>
                    <option value="yearly">Jährlich</option>
                    <option value="once">Einmalig</option>
                </select>
            </div>
            
            <div class="form-group" id="income-date-container">
                <label for="income-date"><i class="fas fa-calendar-day icon-sm"></i> Datum</label>
                <input type="date" id="income-date">
            </div>
            
            <button id="add-income" class="btn-primary">
                <i class="fas fa-plus-circle"></i> Einnahme hinzufügen
            </button>
            
            <h3><i class="fas fa-list icon-md"></i> Deine Einnahmen</h3>
            <div style="overflow-x: auto;">
                <table id="income-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-tag"></i> Bezeichnung</th>
                            <th><i class="fas fa-euro-sign"></i> Betrag</th>
                            <th><i class="fas fa-sync-alt"></i> Häufigkeit</th>
                            <th><i class="fas fa-calendar-day"></i> Nächste Zahlung</th>
                            <th><i class="fas fa-cog"></i> Aktionen</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        
        <!-- Ausgaben Tab -->
        <div id="expenses" class="tab-content card">
            <h2><i class="fas fa-money-bill-transfer icon-md"></i> Ausgaben verwalten</h2>
            
            <div class="form-group">
                <label for="expense-description"><i class="fas fa-tag icon-sm"></i> Bezeichnung</label>
                <input type="text" id="expense-description" placeholder="z.B. Miete">
            </div>
            
            <div class="form-group">
                <label for="expense-amount"><i class="fas fa-euro-sign icon-sm"></i> Betrag (€)</label>
                <input type="number" id="expense-amount" step="0.01" placeholder="z.B. 800.00">
            </div>
            
            <div class="form-group">
                <label for="expense-category"><i class="fas fa-folder icon-sm"></i> Kategorie</label>
                <select id="expense-category">
                </select>
            </div>
            
            <div class="form-group">
                <label for="expense-date"><i class="fas fa-calendar-day icon-sm"></i> Datum</label>
                <input type="date" id="expense-date">
            </div>
            
            <button id="add-expense" class="btn-primary">
                <i class="fas fa-plus-circle"></i> Ausgabe hinzufügen
            </button>
            
            <h3><i class="fas fa-list icon-md"></i> Deine Ausgaben</h3>
            <div style="overflow-x: auto;">
                <table id="expenses-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-tag"></i> Bezeichnung</th>
                            <th><i class="fas fa-euro-sign"></i> Betrag</th>
                            <th><i class="fas fa-folder"></i> Kategorie</th>
                            <th><i class="fas fa-calendar-day"></i> Datum</th>
                            <th><i class="fas fa-cog"></i> Aktionen</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        
        <!-- Wiederkehrende Ausgaben Tab -->
        <div id="recurring" class="tab-content card">
            <h2><i class="fas fa-arrows-rotate icon-md"></i> Wiederkehrende Ausgaben</h2>
            
            <div class="form-group">
                <label for="recurring-description"><i class="fas fa-tag icon-sm"></i> Bezeichnung</label>
                <input type="text" id="recurring-description" placeholder="z.B. Netflix Abo">
            </div>
            
            <div class="form-group">
                <label for="recurring-amount"><i class="fas fa-euro-sign icon-sm"></i> Betrag (€)</label>
                <input type="number" id="recurring-amount" step="0.01" placeholder="z.B. 12.99">
            </div>
            
            <div class="form-group">
                <label for="recurring-category"><i class="fas fa-folder icon-sm"></i> Kategorie</label>
                <select id="recurring-category">
                </select>
            </div>
            
            <div class="form-group">
                <label for="recurring-frequency"><i class="fas fa-sync-alt icon-sm"></i> Häufigkeit</label>
                <select id="recurring-frequency">
                    <option value="monthly">Monatlich</option>
                    <option value="biweekly">Alle 2 Wochen</option>
                    <option value="weekly">Wöchentlich</option>
                    <option value="quarterly">Vierteljährlich</option>
                    <option value="yearly">Jährlich</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="recurring-start-date"><i class="fas fa-calendar-day icon-sm"></i> Startdatum</label>
                <input type="date" id="recurring-start-date">
            </div>
            
            <button id="add-recurring" class="btn-primary">
                <i class="fas fa-plus-circle"></i> Hinzufügen
            </button>
            
            <h3><i class="fas fa-list icon-md"></i> Wiederkehrende Ausgaben</h3>
            <div style="overflow-x: auto;">
                <table id="recurring-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-tag"></i> Bezeichnung</th>
                            <th><i class="fas fa-euro-sign"></i> Betrag</th>
                            <th><i class="fas fa-folder"></i> Kategorie</th>
                            <th><i class="fas fa-sync-alt"></i> Häufigkeit</th>
                            <th><i class="fas fa-calendar-day"></i> Nächste Zahlung</th>
                            <th><i class="fas fa-cog"></i> Aktionen</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        
        <!-- Ratenzahlungen Tab -->
        <div id="installments" class="tab-content card">
            <h2><i class="fas fa-calendar-days icon-md"></i> Ratenzahlungen verwalten</h2>
            
            <div class="form-group">
                <label for="installment-description"><i class="fas fa-tag icon-sm"></i> Bezeichnung</label>
                <input type="text" id="installment-description" placeholder="z.B. Autokauf">
            </div>
            
            <div class="form-group">
                <label for="installment-total"><i class="fas fa-euro-sign icon-sm"></i> Gesamtbetrag (€)</label>
                <input type="number" id="installment-total" step="0.01" placeholder="z.B. 10000.00">
            </div>
            
            <div class="form-group">
                <label for="installment-amount"><i class="fas fa-euro-sign icon-sm"></i> Ratenbetrag (€)</label>
                <input type="number" id="installment-amount" step="0.01" placeholder="z.B. 500.00">
            </div>
            
            <div class="form-group">
                <label for="installment-start"><i class="fas fa-calendar-day icon-sm"></i> Startdatum</label>
                <input type="date" id="installment-start">
            </div>
            
            <div class="form-group">
                <label for="installment-end"><i class="fas fa-calendar-day icon-sm"></i> Enddatum</label>
                <input type="date" id="installment-end">
            </div>
            
            <div class="form-group">
                <label for="installment-frequency"><i class="fas fa-sync-alt icon-sm"></i> Zahlungsintervall</label>
                <select id="installment-frequency">
                    <option value="monthly">Monatlich</option>
                    <option value="biweekly">Alle 2 Wochen</option>
                    <option value="weekly">Wöchentlich</option>
                </select>
            </div>
            
            <button id="add-installment" class="btn-primary">
                <i class="fas fa-plus-circle"></i> Ratenzahlung hinzufügen
            </button>
            
            <h3><i class="fas fa-list icon-md"></i> Aktive Ratenzahlungen</h3>
            <div style="overflow-x: auto;">
                <table id="installments-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-tag"></i> Bezeichnung</th>
                            <th><i class="fas fa-euro-sign"></i> Gesamtbetrag</th>
                            <th><i class="fas fa-euro-sign"></i> Ratenbetrag</th>
                            <th><i class="fas fa-euro-sign"></i> Verbleibend</th>
                            <th><i class="fas fa-calendar-day"></i> Nächste Zahlung</th>
                            <th><i class="fas fa-calendar-day"></i> Enddatum</th>
                            <th><i class="fas fa-cog"></i> Aktionen</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        
        <!-- Einstellungen Tab -->
        <div id="settings" class="tab-content card">
            <h2><i class="fas fa-gear icon-md"></i> Einstellungen</h2>
            
            <!-- Allgemeine Einstellungen -->
            <div id="general" class="subtab-content active">
                <div class="form-group">
                    <label for="monthly-budget"><i class="fas fa-euro-sign icon-sm"></i> Monatliches Budget (€)</label>
                    <input type="number" id="monthly-budget" step="0.01" placeholder="Dein monatliches Budget">
                </div>
                
                <div class="form-group">
                    <label for="savings-percentage"><i class="fas fa-piggy-bank icon-sm"></i> Sparrate (%)</label>
                    <input type="number" id="savings-percentage" min="0" max="100" placeholder="Wie viel % möchtest du sparen?">
                </div>
                
                <div class="form-group">
                    <label for="current-month"><i class="fas fa-calendar-alt icon-sm"></i> Aktueller Monat</label>
                    <input type="month" id="current-month">
                </div>
                
                <button id="save-settings" class="btn-success">
                    <i class="fas fa-save"></i> Einstellungen speichern
                </button>
            </div>
            
            <!-- Kategorie-Einstellungen -->
            <div id="categories" class="subtab-content">
                <h3><i class="fas fa-plus-circle icon-md"></i> Neue Kategorie hinzufügen</h3>
                
                <div class="form-group">
                    <label for="category-name"><i class="fas fa-tag icon-sm"></i> Kategoriename</label>
                    <input type="text" id="category-name" placeholder="z.B. Kleidung">
                </div>
                
				<div class="form-group">
				  <label for="category-color"><i class="fas fa-palette icon-sm"></i> Farbe</label>
				  <input type="color" id="category-color" value="#4361ee">
				</div>

                
                <button id="add-category" class="btn-success mb-3">
                    <i class="fas fa-plus"></i> Kategorie hinzufügen
                </button>
                
                <h3><i class="fas fa-list icon-md"></i> Verfügbare Kategorien</h3>
                <div style="overflow-x: auto;">
                    <table id="categories-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-tag"></i> Name</th>
                                <th><i class="fas fa-palette"></i> Farbe</th>
                                <th><i class="fas fa-info-circle"></i> Typ</th>
                                <th><i class="fas fa-cog"></i> Aktionen</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <a href="logout.php" class="logout-btn" title="Abmelden">
        <i class="fas fa-sign-out-alt"></i>
    </a>
<script>
    // Standardmäßig Dark Mode beim Laden aktivieren
		document.addEventListener('click', async function (e) {
			if (e.target.closest('.delete-btn')) {
				const button = e.target.closest('.delete-btn');
				const row = button.closest('tr');
				const id = button.getAttribute('data-id');

				// Tabellen-Name herausfinden
				if (row && row.closest('#income-table')) {
					if (!confirm('Einnahme wirklich löschen?')) return;
					await deleteItem('income', id);
				} else if (row && row.closest('#expenses-table')) {
					if (!confirm('Ausgabe wirklich löschen?')) return;
					await deleteItem('expense', id);
				} else if (row && row.closest('#recurring-table')) {
					if (!confirm('Wiederkehrende Ausgabe wirklich löschen?')) return;
					await deleteItem('recurring', id);
				} else if (row && row.closest('#installments-table')) {
					if (!confirm('Ratenzahlung wirklich löschen?')) return;
					await deleteItem('installment', id);
				}

				await fetchData();
			}
		});

	
        // Basis-URL für API Aufrufe
        const API_URL = 'api.php';
        
        // Datenstruktur
        let finances = {
            settings: {
                monthlyBudget: 0,
                savingsPercentage: 10,
                currentMonth: new Date().toISOString().slice(0, 7)
            },
            incomes: [],
            expenses: [],
            recurringExpenses: [],
            installments: [],
            categories: [],
            monthlyData: {}
        };

        // Hilfsfunktionen
        function formatCurrency(amount) {
            return new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(amount);
        }

        function getCurrentMonth() {
            return finances.settings.currentMonth || new Date().toISOString().slice(0, 7);
        }

        function getMonthKey(date) {
            if (!date) return '';
            return date.slice(0, 7);
        }
		
		function toggleDarkMode() {
		document.body.classList.toggle('dark-mode');
		}
		
        function calculateNextDate(startDate, frequency) {
            const date = new Date(startDate);
            
            switch (frequency) {
                case 'weekly':
                    date.setDate(date.getDate() + 7);
                    break;
                case 'biweekly':
                    date.setDate(date.getDate() + 14);
                    break;
                case 'monthly':
                    date.setMonth(date.getMonth() + 1);
                    break;
                case 'quarterly':
                    date.setMonth(date.getMonth() + 3);
                    break;
                case 'yearly':
                    date.setFullYear(date.getFullYear() + 1);
                    break;
            }
            
            return date.toISOString().split('T')[0];
        }

        function getFrequencyText(frequency) {
            const texts = {
                'monthly': 'Monatlich',
                'biweekly': 'Alle 2 Wochen',
                'weekly': 'Wöchentlich',
                'quarterly': 'Vierteljährlich',
                'yearly': 'Jährlich',
                'once': 'Einmalig'
            };
            return texts[frequency] || frequency;
        }

        function getCategoryText(category) {
            if (!finances.categories) {
                // Fallback für Standardkategorien
                const texts = {
                };
                return texts[category] || category;
            }
            
            // Wenn category eine ID ist
            if (typeof category === 'number' || !isNaN(category)) {
                const cat = finances.categories.find(c => c.id == category);
                return cat ? cat.name : 'Unbekannt';
            }
            
            // Wenn category ein Name ist
            const cat = finances.categories.find(c => c.name.toLowerCase() === category.toLowerCase());
            return cat ? cat.name : category;
        }
		
		function getCategoryBadge(category) {
			// Wenn keine Kategorien geladen sind, Standardkategorien verwenden
			if (!finances.categories || finances.categories.length === 0) {
				return `<span class="badge badge-${category}">${getCategoryText(category)}</span>`;
			}

			// Finde die Kategorie (kann ID oder Name sein)
			const foundCategory = finances.categories.find(c => 
				c.id == category || c.name.toLowerCase() === category.toLowerCase()
			);

			if (foundCategory) {
				return `<span class="badge" style="background-color: ${foundCategory.color}; color: white;">${foundCategory.name}</span>`;
			}

			// Fallback für unbekannte Kategorien
			return `<span class="badge">${category}</span>`;
		}

        // API Funktionen
		async function fetchData() {
			try {
				showLoader();
				const response = await fetch(`${API_URL}?action=get_data`, {
					credentials: 'include'
				});

				if (!response.ok) throw new Error('Daten konnten nicht geladen werden');

				const data = await response.json();
				finances = data;

				updateUI();
				updateCategorySelects(); // Wichtig: Kategorie-Selects aktualisieren
			} catch (error) {
				console.error('Fehler:', error);
				showAlert('Fehler beim Laden der Daten', 'error');
			} finally {
				hideLoader();
			}
		}
		
		async function deleteCategory(id) {
			if (!confirm("Möchtest du diese Kategorie wirklich löschen?")) return;

			await fetch("api.php?action=delete_category", {
				method: "POST",
				headers: { "Content-Type": "application/json" },
				body: JSON.stringify({ id })
			});

			fetchData();
		}

        async function saveIncome(income) {
            try {
                showLoader();
                const response = await fetch(`${API_URL}?action=add_income`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(income),
                    credentials: 'include'
                });
                
                if (!response.ok) {
                    throw new Error('Einnahme konnte nicht gespeichert werden');
                }
                
                return await response.json();
            } finally {
                hideLoader();
            }
        }

		async function saveExpense(expense) {
			try {
				showLoader();
				const response = await fetch(`${API_URL}?action=add_expense`, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json'
					},
					body: JSON.stringify(expense),
					credentials: 'include'
				});

				if (!response.ok) {
					const errorData = await response.json();
					throw new Error(errorData.error || 'Ausgabe konnte nicht gespeichert werden');
				}

				return await response.json();
			} catch (error) {
				console.error('API Fehler:', error);
				throw error; // Fehler weiterwerfen
			} finally {
				hideLoader();
			}
		}

        async function saveRecurringExpense(expense) {
            try {
                showLoader();
                const response = await fetch(`${API_URL}?action=add_recurring`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(expense),
                    credentials: 'include'
                });
                
                if (!response.ok) {
                    throw new Error('Wiederkehrende Ausgabe konnte nicht gespeichert werden');
                }
                
                return await response.json();
            } finally {
                hideLoader();
            }
        }

        async function saveInstallment(installment) {
            try {
                showLoader();
                const response = await fetch(`${API_URL}?action=add_installment`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(installment),
                    credentials: 'include'
                });
                
                if (!response.ok) {
                    throw new Error('Ratenzahlung konnte nicht gespeichert werden');
                }
                
                return await response.json();
            } finally {
                hideLoader();
            }
        }

        async function deleteItem(type, id) {
            try {
                showLoader();
                const response = await fetch(`${API_URL}?action=delete&type=${type}&id=${id}`, {
                    method: 'DELETE',
                    credentials: 'include'
                });
                
                if (!response.ok) {
                    throw new Error('Eintrag konnte nicht gelöscht werden');
                }
                
                return await response.json();
            } finally {
                hideLoader();
            }
        }

        async function saveSettings(settings) {
            try {
                showLoader();
                const response = await fetch(`${API_URL}?action=save_settings`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(settings),
                    credentials: 'include'
                });
                
                if (!response.ok) {
                    throw new Error('Einstellungen konnten nicht gespeichert werden');
                }
                
                return await response.json();
            } finally {
                hideLoader();
            }
        }

        // UI Funktionen
		function updateUI() {
			updateSummary();
			updateIncomeTable();
			updateExpensesTable();
			updateRecurringTable();
			updateInstallmentsTable();
			updateUpcomingExpenses();
			updateSettingsForm();
			updateCategoriesTable();
			updateCategorySelects();
		}

        function updateSettingsForm() {
            document.getElementById('monthly-budget').value = finances.settings.monthly_budget || 0;
            document.getElementById('savings-percentage').value = finances.settings.savings_percentage || 10;
            document.getElementById('current-month').value = finances.settings.current_month || new Date().toISOString().slice(0, 7);
        }

		function updateCategoriesTable() {
			const tbody = document.querySelector('#categories-table tbody');
			tbody.innerHTML = '';

			if (!finances.categories) return;

			finances.categories.forEach((category) => {
				// Testweise alles als benutzerdefiniert markieren
				category.is_default = false;

				const tr = document.createElement('tr');

				const isUserCategory = !category.is_default;

				tr.innerHTML = `
					<td>${category.name}</td>
					<td>
						<span class="badge" style="background-color: ${category.color}; color: white;">
							${category.color}
						</span>
					</td>
					<td>${isUserCategory ? 'Benutzerdefiniert' : 'Standard'}</td>
					<td>
						${isUserCategory ? `
						<button class="delete-btn" type="button" onclick="deleteCategory(${category.id})" title="Löschen">
						  <i class="fas fa-trash-alt"></i>
						</button>
						` : '<span class="text-muted">Standard</span>'}
					</td>
				`;

				tbody.appendChild(tr);
			});
		}



		function updateCategorySelects() {
			const selects = [
				document.getElementById('expense-category'),
				document.getElementById('recurring-category')
			];

			if (!finances.categories || finances.categories.length === 0) return;

			selects.forEach(select => {
				if (!select) return;

				// Behalte die Standardoptionen
				const defaultOptions = Array.from(select.querySelectorAll('option[value="housing"], option[value="food"], option[value="transport"], option[value="health"], option[value="leisure"], option[value="other"]'));

				// Lösche alle anderen Optionen
				Array.from(select.options).forEach(option => {
					if (!defaultOptions.includes(option)) {
						select.removeChild(option);
					}
				});

				// Füge benutzerdefinierte Kategorien hinzu
				finances.categories.forEach(category => {
					if (!category.is_default) {
						const option = document.createElement('option');
						option.value = category.id;
						option.textContent = category.name;
						select.appendChild(option);
					}
				});
			});
		}

		function updateSummary() {
			const currentMonth = getCurrentMonth();
			let cumulativeBalance = 0;
			let monthlyDetails = [];

			// 1. Kumulierten Saldo berechnen
			const allMonths = Object.keys(finances.monthlyData).sort();
			const currentMonthIndex = allMonths.indexOf(currentMonth);

			if (currentMonthIndex > 0) {
				const prevMonths = allMonths.slice(0, currentMonthIndex);
				prevMonths.forEach(month => {
					const data = finances.monthlyData[month];
					const balance = (data.income || 0) - (data.expenses || 0);
					cumulativeBalance += balance;

					monthlyDetails.push({
						month,
						income: data.income || 0,
						expenses: data.expenses || 0,
						balance
					});
				});
			}

			// 2. Berechnung der Ausgaben - verwende dieselbe Logik wie in updateUpcomingExpenses()
			const today = new Date();
			today.setHours(0, 0, 0, 0);
			const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
			endOfMonth.setHours(23, 59, 59, 999);

			// Bereits getätigte Ausgaben
			const pastExpenses = finances.expenses
				.filter(expense => {
					const expenseDate = new Date(expense.date);
					return expenseDate < today && getMonthKey(expense.date) === currentMonth;
				})
				.reduce((sum, expense) => sum + parseFloat(expense.amount), 0);

			// Berechne ausstehende Ausgaben mit derselben Methode wie in updateUpcomingExpenses()
			const { currentMonthExpenses, nextMonthExpenses } = calculateUpcomingExpenses();
			const totalUpcomingThisMonth = currentMonthExpenses.reduce((sum, exp) => sum + exp.amount, 0);
			const totalExpensesThisMonth = pastExpenses + totalUpcomingThisMonth;

			// 3. Einnahmen und Sparrate
			const currentIncome = finances.monthlyData[currentMonth]?.income || 0;
			const savingsPercentage = finances.settings.savings_percentage || 0;
			const savingsAmount = currentIncome * (savingsPercentage / 100);

			// 4. Budgetberechnungen
			const remainingBudget = (cumulativeBalance + currentIncome) - totalExpensesThisMonth;
			const nextMonthProjection = remainingBudget - savingsAmount;

			// 5. HTML generieren
			document.querySelector('#overview .summary').innerHTML = `
				<!-- Kumulierter Übertrag -->
				<div class="summary-item ${cumulativeBalance >= 0 ? 'balance-positive' : 'balance-negative'}">
					<h3><i class="fas fa-calendar-check"></i> Kumulierter Übertrag</h3>
					<p class="${cumulativeBalance >= 0 ? 'positive' : 'negative'}">
						${formatCurrency(cumulativeBalance)}
						<small>aus ${monthlyDetails.length} Monaten</small>
					</p>
				</div>

				<!-- Einnahmen -->
				<div class="summary-item income">
					<h3><i class="fas fa-money-bill-wave"></i> Einnahmen (${currentMonth})</h3>
					<p class="positive">${formatCurrency(currentIncome)}</p>
				</div>

				<!-- Bereits getätigte Ausgaben -->
				<div class="summary-item expense">
					<h3><i class="fas fa-receipt"></i> Bereits bezahlte Ausgaben</h3>
					<p class="negative">-${formatCurrency(pastExpenses)}</p>
				</div>

				<!-- Noch ausstehende Ausgaben -->
				<div class="summary-item upcoming">
					<h3><i class="fas fa-calendar-day"></i> Noch ausstehende Ausgaben</h3>
					<p class="upcoming-amount">-${formatCurrency(totalUpcomingThisMonth)}</p>
				</div>

				<!-- Gesamtausgaben -->
				<div class="summary-item expense">
					<h3><i class="fas fa-shopping-cart"></i> Gesamtausgaben</h3>
					<span class="summary-subtitle">(${currentMonth})</span>
					<p class="negative">-${formatCurrency(totalExpensesThisMonth)}</p>
				</div>

				<!-- Geplante Sparrate -->
				<div class="summary-item savings">
					<h3><i class="fas fa-piggy-bank"></i> Geplante Sparrate (${savingsPercentage}%)</h3>
					<p class="planned-savings">( ${formatCurrency(savingsAmount)} )</p>
				</div>

				<!-- Verbleibendes Budget -->
				<div class="summary-item ${remainingBudget >= 0 ? 'balance-positive' : 'balance-negative'}">
					<h3><i class="fas fa-wallet"></i> Verbleibendes Budget</h3>
					<p class="${remainingBudget >= 0 ? 'positive' : 'negative'}">
						${formatCurrency(remainingBudget)}
					</p>
				</div>

				<!-- Prognose nächster Monat -->
				<div class="summary-item ${nextMonthProjection >= 0 ? 'balance-positive' : 'balance-negative'}">
					<h3><i class="fas fa-chart-line"></i> Prognose nächster Monat</h3>
					<p class="${nextMonthProjection >= 0 ? 'positive' : 'negative'}">
						${formatCurrency(nextMonthProjection)}
						<small>nach Sparrate</small>
					</p>
				</div>
				
<div class="summary-item full-width">
	<h3><i class="fas fa-chart-line icon-md"></i> Finanzverlauf</h3>
	<div style="position: relative; width: 100%; height: 300px;">
		<canvas id="monthlyChart"></canvas>
	</div>
</div>

				
				<!-- Monatsdetails -->
				<div class="summary-item full-width">
					<details class="monthly-details">
						<summary><i class="fas fa-history"></i> Monatliche Details</summary>
						
						<div class="month-details">
							<!-- Kopfzeile -->
							<div class="month-row header">
								<span>📅 Monat</span>
								<span>💰 Einnahmen</span>
								<span>🧾 Ausgaben</span>
								<span>💼 Saldo</span>
							</div>

							<!-- Monatsdaten -->
							${monthlyDetails.map(m => `
								<div class="month-row ${m.balance >= 0 ? 'balance-positive' : 'balance-negative'}">
									<span>${m.month}</span>
									<span class="positive">+ ${formatCurrency(m.income)}</span>
									<span class="negative">- ${formatCurrency(m.expenses)}</span>
									<span class="${m.balance >= 0 ? 'positive' : 'negative'}">
										= ${formatCurrency(m.balance)}
									</span>
								</div>
							`).join('')}
						</div>
					</details>
				</div>
			`;
		}

		// Neue Hilfsfunktion - wird sowohl von updateSummary() als auch updateUpcomingExpenses() genutzt
		function calculateUpcomingExpenses() {
			const today = new Date();
			today.setHours(0, 0, 0, 0);
			
			const currentMonth = getCurrentMonth();
			const endOfCurrentMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
			endOfCurrentMonth.setHours(23, 59, 59, 999);
			
			const startOfNextMonth = new Date(today.getFullYear(), today.getMonth() + 1, 1);
			startOfNextMonth.setHours(0, 0, 0, 0);
			
			const endOfNextMonth = new Date(today.getFullYear(), today.getMonth() + 2, 0);
			endOfNextMonth.setHours(23, 59, 59, 999);

			const currentMonthExpenses = [];
			const nextMonthExpenses = [];

			// Hilfsfunktion zum Hinzufügen von Ausgaben
			const addExpense = (item, date, type) => {
				const amount = parseFloat(item.amount || item.installment_amount);
				const expenseDate = new Date(date);
				
				if (expenseDate >= today && expenseDate <= endOfCurrentMonth) {
					currentMonthExpenses.push({
						description: item.description,
						amount: amount,
						category: item.category || 'Ratenzahlung',
						type: type,
						rawDate: expenseDate.toISOString().split('T')[0],
						daysUntil: Math.floor((expenseDate - today) / (1000 * 60 * 60 * 24))
					});
				} else if (expenseDate >= startOfNextMonth && expenseDate <= endOfNextMonth) {
					nextMonthExpenses.push({
						description: item.description,
						amount: amount,
						category: item.category || 'Ratenzahlung',
						type: type,
						rawDate: expenseDate.toISOString().split('T')[0],
						daysUntil: Math.floor((expenseDate - today) / (1000 * 60 * 60 * 24))
					});
				}
			};

			// 1. Einmalige Ausgaben
			finances.expenses.forEach(expense => {
				const expenseDate = new Date(expense.date);
				addExpense(expense, expenseDate, 'Einmalig');
			});

			// 2. Wiederkehrende Ausgaben
			finances.recurringExpenses.forEach(expense => {
				let currentDate = new Date(expense.next_date || expense.start_date);
				let count = 0;
				const maxIterations = 12;

				while (count < maxIterations && currentDate <= endOfNextMonth) {
					addExpense(expense, currentDate, 'Wiederkehrend');
					
					// Nächstes Datum berechnen
					currentDate = new Date(currentDate);
					switch (expense.frequency) {
						case 'weekly': currentDate.setDate(currentDate.getDate() + 7); break;
						case 'biweekly': currentDate.setDate(currentDate.getDate() + 14); break;
						case 'monthly': currentDate.setMonth(currentDate.getMonth() + 1); break;
						case 'quarterly': currentDate.setMonth(currentDate.getMonth() + 3); break;
						case 'yearly': currentDate.setFullYear(currentDate.getFullYear() + 1); break;
					}
					count++;
				}
			});

			// 3. Ratenzahlungen
			finances.installments.forEach(installment => {
				let currentDate = new Date(installment.next_payment_date);
				const endDate = new Date(installment.end_date);
				let count = 0;
				const maxIterations = 12;

				if (currentDate <= endDate) {
					while (count < maxIterations && currentDate <= endOfNextMonth && currentDate <= endDate) {
						addExpense(installment, currentDate, 'Ratenzahlung');
						
						currentDate = new Date(currentDate);
						switch (installment.frequency) {
							case 'weekly': currentDate.setDate(currentDate.getDate() + 7); break;
							case 'biweekly': currentDate.setDate(currentDate.getDate() + 14); break;
							case 'monthly': currentDate.setMonth(currentDate.getMonth() + 1); break;
						}
						count++;
					}
				}
			});

			// Sortieren nach Datum
			currentMonthExpenses.sort((a, b) => new Date(a.rawDate) - new Date(b.rawDate));
			nextMonthExpenses.sort((a, b) => new Date(a.rawDate) - new Date(b.rawDate));

			return { currentMonthExpenses, nextMonthExpenses };
		}

		function updateUpcomingExpenses() {
			// Monatsnamen setzen
			const currentMonthName = new Date().toISOString().slice(0, 7);
			const nextMonthName = new Date(new Date().getFullYear(), new Date().getMonth() + 1, 1)
								  .toISOString().slice(0, 7);
			
			document.getElementById('current-month-name').textContent = currentMonthName;
			document.getElementById('next-month-name').textContent = nextMonthName;
			
			const currentMonthTbody = document.querySelector('#current-month-expenses tbody');
			const nextMonthTbody = document.querySelector('#next-month-expenses tbody');
			currentMonthTbody.innerHTML = '';
			nextMonthTbody.innerHTML = '';

			// Berechne die ausstehenden Ausgaben (verwendet dieselbe Funktion wie updateSummary())
			const { currentMonthExpenses, nextMonthExpenses } = calculateUpcomingExpenses();
			const totalCurrentMonth = currentMonthExpenses.reduce((sum, exp) => sum + exp.amount, 0);
			const totalNextMonth = nextMonthExpenses.reduce((sum, exp) => sum + exp.amount, 0);

			// Funktion zum Befüllen einer Tabelle
			const fillTable = (tbody, expenses, totalAmount) => {
				if (expenses.length > 0) {
					expenses.forEach(item => {
						const tr = document.createElement('tr');
						const daysUntilText = item.daysUntil === 0 ? 'Heute' : 
											item.daysUntil === 1 ? 'Morgen' : 
											`in ${item.daysUntil} Tagen`;

						tr.innerHTML = `
						  <td>${category.name}</td>
						  <td>
							  <span class="badge" style="background-color: ${category.color}; color: white;">
								  ${category.color}
							  </span>
						  </td>
						  <td>${category.is_default ? 'Standard' : 'Benutzerdefiniert'}</td>
						  <td>
							  ${!category.is_default ? `
								  <button class="edit-btn" onclick="editCategory(${category.id})" title="Bearbeiten">
									  <i class="fas fa-edit"></i>
								  </button>
								  <button class="icon-btn" onclick="confirmDelete(${category.id})" title="Löschen">
									  <i class="fas fa-trash-alt"></i>
								  </button>
							  ` : '<span class="text-muted">Standard</span>'}
						  </td>
						`;

						if (item.daysUntil === 0) tr.classList.add('due-today');
						else if (item.daysUntil <= 3) tr.classList.add('due-soon');

						tbody.appendChild(tr);
					});

					// Gesamtzeile
					const footerRow = document.createElement('tr');
					footerRow.classList.add('upcoming-total');
					footerRow.innerHTML = `
						<td colspan="3" class="total-label">
							<div class="total-label-content">
								<i class="fas fa-calendar-alt"></i>
								<span>Gesamt:</span>
							</div>
						</td>
						<td class="total-amount">
							<span>${formatCurrency(totalAmount)}</span>
						</td>
					`;
					tbody.appendChild(footerRow);
				} else {
					tbody.innerHTML = `
						<tr class="no-expenses">
							<td colspan="4">
								<div class="no-expenses-content">
									<i class="far fa-smile"></i>
									<span>Keine Ausgaben</span>
								</div>
							</td>
						</tr>
					`;
				}
			};

			// Tabellen befüllen
			fillTable(currentMonthTbody, currentMonthExpenses, totalCurrentMonth);
			fillTable(nextMonthTbody, nextMonthExpenses, totalNextMonth);
		}

		

        function updateIncomeTable() {
            const tbody = document.querySelector('#income-table tbody');
            tbody.innerHTML = '';
            
            finances.incomes.forEach((income) => {
                const tr = document.createElement('tr');
                
                tr.innerHTML = `
                    <td>${income.description}</td>
                    <td>${formatCurrency(parseFloat(income.amount))}</td>
                    <td>${getFrequencyText(income.frequency)}</td>
                    <td>${income.next_date ? new Date(income.next_date).toLocaleDateString('de-DE') : '-'}</td>
					<td>
					  <button class="delete-btn" data-id="${income.id}" title="Löschen">
						<i class="fas fa-trash-alt"></i>
					  </button>
					</td>
                `;
                
                tbody.appendChild(tr);
            });
            
            document.querySelectorAll('#income-table .btn-danger').forEach(button => {
                button.addEventListener('click', async function() {
                    if (!confirm('Einnahme wirklich löschen?')) return;
                    
                    const id = this.getAttribute('data-id');
                    
                    try {
                        await deleteItem('income', id);
                        await fetchData();
						console.log("Monatsdaten:", finances.monthlyData);
						console.log("Aktueller Monat:", currentMonth);
                        showAlert('Einnahme erfolgreich gelöscht', 'success');
                    } catch (error) {
                        console.error('Fehler:', error);
                        showAlert('Fehler beim Löschen der Einnahme', 'error');
                    }
                });
            });
        }

        function updateExpensesTable() {
            const tbody = document.querySelector('#expenses-table tbody');
            tbody.innerHTML = '';
            
            finances.expenses.forEach((expense) => {
                const tr = document.createElement('tr');
                
                tr.innerHTML = `
                    <td>${expense.description}</td>
                    <td>${formatCurrency(parseFloat(expense.amount))}</td>
                    <td>${getCategoryBadge(expense.category)}</td>
                    <td>${new Date(expense.date).toLocaleDateString('de-DE')}</td>
					<td>
					  <button class="delete-btn" data-id="${expense.id}" title="Löschen">
						<i class="fas fa-trash-alt"></i>
					  </button>
					</td>
                `;
                
                tbody.appendChild(tr);
            });
            
            document.querySelectorAll('#expenses-table .btn-danger').forEach(button => {
                button.addEventListener('click', async function() {
                    if (!confirm('Ausgabe wirklich löschen?')) return;
                    
                    const id = this.getAttribute('data-id');
                    
                    try {
                        await deleteItem('expense', id);
                        await fetchData();
                        showAlert('Ausgabe erfolgreich gelöscht', 'success');
                    } catch (error) {
                        console.error('Fehler:', error);
                        showAlert('Fehler beim Löschen der Ausgabe', 'error');
                    }
                });
            });
        }

        function updateRecurringTable() {
            const tbody = document.querySelector('#recurring-table tbody');
            tbody.innerHTML = '';
            
            finances.recurringExpenses.forEach((expense) => {
                const tr = document.createElement('tr');
                
                tr.innerHTML = `
                    <td>${expense.description}</td>
                    <td>${formatCurrency(parseFloat(expense.amount))}</td>
                    <td>${getCategoryBadge(expense.category)}</td>
                    <td>${getFrequencyText(expense.frequency)}</td>
                    <td>${expense.next_date ? new Date(expense.next_date).toLocaleDateString('de-DE') : '-'}</td>
					<td>
					  <button class="delete-btn" data-id="${expense.id}" title="Löschen">
						<i class="fas fa-trash-alt"></i>
					  </button>
					</td>
                `;
                
                tbody.appendChild(tr);
            });
            
            document.querySelectorAll('#recurring-table .btn-danger').forEach(button => {
                button.addEventListener('click', async function() {
                    if (!confirm('Wiederkehrende Ausgabe wirklich löschen?')) return;
                    
                    const id = this.getAttribute('data-id');
                    
                    try {
                        await deleteItem('recurring', id);
                        await fetchData();
                        showAlert('Wiederkehrende Ausgabe erfolgreich gelöscht', 'success');
                    } catch (error) {
                        console.error('Fehler:', error);
                        showAlert('Fehler beim Löschen der wiederkehrenden Ausgabe', 'error');
                    }
                });
            });
        }

        function updateInstallmentsTable() {
            const tbody = document.querySelector('#installments-table tbody');
            tbody.innerHTML = '';
            
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            finances.installments.forEach((installment) => {
                const endDate = new Date(installment.end_date);
                if (endDate >= today) {
                    const tr = document.createElement('tr');
                    
                    tr.innerHTML = `
                        <td>${installment.description}</td>
                        <td>${formatCurrency(parseFloat(installment.total_amount))}</td>
                        <td>${formatCurrency(parseFloat(installment.installment_amount))}</td>
                        <td>${formatCurrency(parseFloat(installment.remaining_amount))}</td>
                        <td>${new Date(installment.next_payment_date).toLocaleDateString('de-DE')}</td>
                        <td>${new Date(installment.end_date).toLocaleDateString('de-DE')}</td>
                        <td>
							<button class="delete-btn" type="button" data-id="${installment.id}" title="Ratenzahlung löschen">
							  <i class="fas fa-trash-alt"></i>
							</button>
                        </td>
                    `;
                    
                    tbody.appendChild(tr);
                }
            });
            
			document.querySelectorAll('#installments-table .delete-btn').forEach(button => {
				button.addEventListener('click', async function () {
					if (!confirm('Ratenzahlung wirklich löschen?')) return;
					const id = this.getAttribute('data-id');
					await deleteItem('installment', id);
					await fetchData();
					showAlert('Ratenzahlung erfolgreich gelöscht', 'success');
				});
			});
        }

function updateUpcomingExpenses() {
    // Monatsnamen setzen
    const currentMonthName = new Date().toISOString().slice(0, 7);
    const nextMonthName = new Date(new Date().getFullYear(), new Date().getMonth() + 1, 1)
                          .toISOString().slice(0, 7);
    
    document.getElementById('current-month-name').textContent = currentMonthName;
    document.getElementById('next-month-name').textContent = nextMonthName;
    
    const currentMonthTbody = document.querySelector('#current-month-expenses tbody');
    const nextMonthTbody = document.querySelector('#next-month-expenses tbody');
    currentMonthTbody.innerHTML = '';
    nextMonthTbody.innerHTML = '';

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    // Ende des aktuellen Monats
    const endOfCurrentMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    endOfCurrentMonth.setHours(23, 59, 59, 999);
    
    // Beginn des nächsten Monats
    const startOfNextMonth = new Date(today.getFullYear(), today.getMonth() + 1, 1);
    startOfNextMonth.setHours(0, 0, 0, 0);
    
    // Ende des nächsten Monats
    const endOfNextMonth = new Date(today.getFullYear(), today.getMonth() + 2, 0);
    endOfNextMonth.setHours(23, 59, 59, 999);

    const currentMonthExpenses = [];
    const nextMonthExpenses = [];
    let totalCurrentMonth = 0;
    let totalNextMonth = 0;

    // Hilfsfunktion zum Hinzufügen von Ausgaben
    const addExpense = (item, date, type, frequency = null) => {
        const amount = parseFloat(item.amount || item.installment_amount);
        const expenseDate = new Date(date);
        
        if (expenseDate >= today && expenseDate <= endOfCurrentMonth) {
            currentMonthExpenses.push({
                description: item.description,
                amount: amount,
                category: item.category || 'Ratenzahlung',
                type: type,
                rawDate: expenseDate.toISOString().split('T')[0],
                daysUntil: Math.floor((expenseDate - today) / (1000 * 60 * 60 * 24))
            });
            totalCurrentMonth += amount;
        } else if (expenseDate >= startOfNextMonth && expenseDate <= endOfNextMonth) {
            nextMonthExpenses.push({
                description: item.description,
                amount: amount,
                category: item.category || 'Ratenzahlung',
                type: type,
                rawDate: expenseDate.toISOString().split('T')[0],
                daysUntil: Math.floor((expenseDate - today) / (1000 * 60 * 60 * 24))
            });
            totalNextMonth += amount;
        }
    };

    // 1. Einmalige Ausgaben
    finances.expenses.forEach(expense => {
        const expenseDate = new Date(expense.date);
        addExpense(expense, expenseDate, 'Einmalig');
    });

    // 2. Wiederkehrende Ausgaben
    finances.recurringExpenses.forEach(expense => {
        let currentDate = new Date(expense.next_date || expense.start_date);
        let count = 0;
        const maxIterations = 12;

        while (count < maxIterations && currentDate <= endOfNextMonth) {
            addExpense(expense, currentDate, 'Wiederkehrend', expense.frequency);
            
            // Nächstes Datum berechnen
            currentDate = new Date(currentDate);
            switch (expense.frequency) {
                case 'weekly': currentDate.setDate(currentDate.getDate() + 7); break;
                case 'biweekly': currentDate.setDate(currentDate.getDate() + 14); break;
                case 'monthly': currentDate.setMonth(currentDate.getMonth() + 1); break;
                case 'quarterly': currentDate.setMonth(currentDate.getMonth() + 3); break;
                case 'yearly': currentDate.setFullYear(currentDate.getFullYear() + 1); break;
            }
            count++;
        }
    });

    // 3. Ratenzahlungen - KORRIGIERTE VERSION
    finances.installments.forEach(installment => {
        let currentDate = new Date(installment.next_payment_date);
        const endDate = new Date(installment.end_date);
        let count = 0;
        const maxIterations = 12;

        // Nur aktive Ratenzahlungen berücksichtigen
        if (currentDate <= endDate) {
            while (count < maxIterations && currentDate <= endOfNextMonth && currentDate <= endDate) {
                addExpense(installment, currentDate, 'Ratenzahlung', installment.frequency);
                
                currentDate = new Date(currentDate);
                switch (installment.frequency) {
                    case 'weekly': currentDate.setDate(currentDate.getDate() + 7); break;
                    case 'biweekly': currentDate.setDate(currentDate.getDate() + 14); break;
                    case 'monthly': currentDate.setMonth(currentDate.getMonth() + 1); break;
                }
                count++;
            }
        }
    });

    // Sortieren nach Datum
    currentMonthExpenses.sort((a, b) => new Date(a.rawDate) - new Date(b.rawDate));
    nextMonthExpenses.sort((a, b) => new Date(a.rawDate) - new Date(b.rawDate));

    // Funktion zum Befüllen einer Tabelle
    const fillTable = (tbody, expenses, totalAmount) => {
        if (expenses.length > 0) {
            expenses.forEach(item => {
                const tr = document.createElement('tr');
                const daysUntilText = item.daysUntil === 0 ? 'Heute' : 
                                    item.daysUntil === 1 ? 'Morgen' : 
                                    `in ${item.daysUntil} Tagen`;

                tr.innerHTML = `
                    <td>
                        <div class="expense-description">
                            <strong>${item.description}</strong>
                            <small class="expense-type">${item.type}</small>
                        </div>
                    </td>
                    <td class="amount-cell">
                        <span class="expense-amount">${formatCurrency(item.amount)}</span>
                    </td>
                    <td>
                        ${getCategoryBadge(item.category)}
                    </td>
                    <td>
                        <div class="date-info">
                            <span class="expense-date">${new Date(item.rawDate).toLocaleDateString('de-DE')}</span>
                            <span class="days-until ${item.daysUntil <= 3 ? 'soon' : ''}">${daysUntilText}</span>
                        </div>
                    </td>
                `;

                if (item.daysUntil === 0) tr.classList.add('due-today');
                else if (item.daysUntil <= 3) tr.classList.add('due-soon');

                tbody.appendChild(tr);
            });

            // Gesamtzeile
            const footerRow = document.createElement('tr');
            footerRow.classList.add('upcoming-total');
            footerRow.innerHTML = `
                <td colspan="3" class="total-label">
                    <div class="total-label-content">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Gesamt:</span>
                    </div>
                </td>
                <td class="total-amount">
                    <span>${formatCurrency(totalAmount)}</span>
                </td>
            `;
            tbody.appendChild(footerRow);
        } else {
            tbody.innerHTML = `
                <tr class="no-expenses">
                    <td colspan="4">
                        <div class="no-expenses-content">
                            <i class="far fa-smile"></i>
                            <span>Keine Ausgaben</span>
                        </div>
                    </td>
                </tr>
            `;
        }
    };

    // Tabellen befüllen
    fillTable(currentMonthTbody, currentMonthExpenses, totalCurrentMonth);
    fillTable(nextMonthTbody, nextMonthExpenses, totalNextMonth);
}


        // Tab Funktionen
		function initTabs() {
			const tabs = document.querySelectorAll('.tab');
			const tabContents = document.querySelectorAll('.tab-content');
			
			tabs.forEach(tab => {
				tab.addEventListener('click', () => {
					// Alle Tabs deaktivieren
					tabs.forEach(t => t.classList.remove('active'));
					tabContents.forEach(c => c.classList.remove('active'));
					
					// Aktiven Tab aktivieren
					tab.classList.add('active');
					const tabId = tab.getAttribute('data-tab');
					document.getElementById(tabId).classList.add('active');
					
					// Mobile Menü schließen (falls vorhanden)
					const tabsContainer = document.querySelector('.tabs-container');
					if (tabsContainer) {
						tabsContainer.classList.remove('active');
					}
				});
			});
			
			// Standardmäßig ersten Tab aktivieren, falls keiner aktiv ist
			if (!document.querySelector('.tab.active')) {
				tabs[0]?.classList.add('active');
				tabContents[0]?.classList.add('active');
			}
		}

        // Formular Funktionen
        function initForms() {
            // Einnahmen hinzufügen
            document.getElementById('add-income').addEventListener('click', async function() {
                const description = document.getElementById('income-description').value.trim();
                const amount = parseFloat(document.getElementById('income-amount').value);
                const frequency = document.getElementById('income-frequency').value;
                const date = document.getElementById('income-date').value;
                
                if (!description || isNaN(amount)) {
                    showAlert('Bitte fülle alle Felder korrekt aus', 'error');
                    return;
                }
                
                try {
                    await saveIncome({
                        description,
                        amount,
                        frequency,
                        nextDate: frequency === 'once' ? date : (frequency === 'monthly' ? new Date().toISOString().split('T')[0] : date)
                    });
                    
                    await fetchData();
                    
                    document.getElementById('income-description').value = '';
                    document.getElementById('income-amount').value = '';
                    
                    showAlert('Einnahme erfolgreich hinzugefügt', 'success');
                } catch (error) {
                    console.error('Fehler:', error);
                    showAlert('Fehler beim Speichern der Einnahme', 'error');
                }
            });
            
            // Ausgaben hinzufügen
			document.getElementById('add-expense').addEventListener('click', async function() {
				const description = document.getElementById('expense-description').value.trim();
				const amount = parseFloat(document.getElementById('expense-amount').value);
				const category = document.getElementById('expense-category').value;
				const date = document.getElementById('expense-date').value;

				if (!description || isNaN(amount) || !category || !date) {
					showAlert('Bitte fülle alle Felder korrekt aus', 'error');
					return;
				}

				try {
					const expenseData = {
						description,
						amount,
						category,
						date
					};

					console.log("Sende Ausgabe:", expenseData); // Debug

					await saveExpense(expenseData);
					await fetchData();

					document.getElementById('expense-description').value = '';
					document.getElementById('expense-amount').value = '';

					showAlert('Ausgabe erfolgreich hinzugefügt', 'success');
				} catch (error) {
					console.error('Fehler:', error);
					showAlert(error.message || 'Fehler beim Speichern der Ausgabe', 'error');
				}
			});
            
            // Wiederkehrende Ausgaben hinzufügen
            document.getElementById('add-recurring').addEventListener('click', async function() {
                const description = document.getElementById('recurring-description').value.trim();
                const amount = parseFloat(document.getElementById('recurring-amount').value);
                const category = document.getElementById('recurring-category').value;
                const frequency = document.getElementById('recurring-frequency').value;
                const startDate = document.getElementById('recurring-start-date').value;
                
                if (!description || isNaN(amount)) {
                    showAlert('Bitte fülle alle Felder korrekt aus', 'error');
                    return;
                }
                
                try {
                    await saveRecurringExpense({
                        description,
                        amount,
                        category,
                        frequency,
                        startDate,
                        nextDate: calculateNextDate(startDate, frequency)
                    });
                    
                    await fetchData();
                    
                    document.getElementById('recurring-description').value = '';
                    document.getElementById('recurring-amount').value = '';
                    
                    showAlert('Wiederkehrende Ausgabe erfolgreich hinzugefügt', 'success');
                } catch (error) {
                    console.error('Fehler:', error);
                    showAlert('Fehler beim Speichern der wiederkehrenden Ausgabe', 'error');
                }
            });
            
            // Ratenzahlungen hinzufügen
            document.getElementById('add-installment').addEventListener('click', async function() {
                const description = document.getElementById('installment-description').value.trim();
                const totalAmount = parseFloat(document.getElementById('installment-total').value);
                const installmentAmount = parseFloat(document.getElementById('installment-amount').value);
                const startDate = document.getElementById('installment-start').value;
                const endDate = document.getElementById('installment-end').value;
                const frequency = document.getElementById('installment-frequency').value;
                
                if (!description || isNaN(totalAmount) || isNaN(installmentAmount) || !startDate || !endDate) {
                    showAlert('Bitte fülle alle Felder korrekt aus', 'error');
                    return;
                }
                
                if (new Date(startDate) > new Date(endDate)) {
                    showAlert('Enddatum muss nach dem Startdatum liegen', 'error');
                    return;
                }
                
                if (installmentAmount > totalAmount) {
                    showAlert('Ratenbetrag darf nicht größer als Gesamtbetrag sein', 'error');
                    return;
                }
                
                try {
                    await saveInstallment({
                        description,
                        totalAmount,
                        installmentAmount,
                        startDate,
                        endDate,
                        frequency
                    });
                    
                    await fetchData();
                    
                    document.getElementById('installment-description').value = '';
                    document.getElementById('installment-total').value = '';
                    document.getElementById('installment-amount').value = '';
                    
                    showAlert('Ratenzahlung erfolgreich hinzugefügt', 'success');
                } catch (error) {
                    console.error('Fehler:', error);
                    showAlert('Fehler beim Speichern der Ratenzahlung', 'error');
                }
            });
            
            // Einstellungen speichern
            document.getElementById('save-settings').addEventListener('click', async function() {
                const monthlyBudget = parseFloat(document.getElementById('monthly-budget').value) || 0;
                const savingsPercentage = parseInt(document.getElementById('savings-percentage').value) || 0;
                const currentMonth = document.getElementById('current-month').value;
                
                try {
                    await saveSettings({
                        monthlyBudget,
                        savingsPercentage,
                        currentMonth
                    });
                    
                    await fetchData();
                    showAlert('Einstellungen erfolgreich gespeichert', 'success');
                } catch (error) {
                    console.error('Fehler:', error);
                    showAlert('Fehler beim Speichern der Einstellungen', 'error');
                }
            });
            
            // Häufigkeit ändern
            document.getElementById('income-frequency').addEventListener('change', function() {
                const showDate = this.value !== 'monthly';
                document.getElementById('income-date-container').style.display = showDate ? 'block' : 'none';
            });
            
            // Sub-Tabs für Einstellungen
            document.querySelectorAll('[data-subtab]').forEach(tab => {
                tab.addEventListener('click', () => {
                    document.querySelectorAll('[data-subtab]').forEach(t => t.classList.remove('active'));
                    document.querySelectorAll('.subtab-content').forEach(c => c.classList.remove('active'));
                    
                    tab.classList.add('active');
                    const subtabId = tab.getAttribute('data-subtab');
                    document.getElementById(subtabId).classList.add('active');
                });
            });
            
            // Kategorie hinzufügen
            document.getElementById('add-category').addEventListener('click', async function() {
                const name = document.getElementById('category-name').value.trim();
                const color = document.getElementById('category-color').value;
                
                if (!name) {
                    showAlert('Bitte geben Sie einen Kategorienamen ein', 'error');
                    return;
                }
                
                try {
                    const response = await fetch(`${API_URL}?action=add_category`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ name, color }),
                        credentials: 'include'
                    });
                    
                    if (!response.ok) {
                        throw new Error('Kategorie konnte nicht hinzugefügt werden');
                    }
                    
                    await fetchData();
                    
                    document.getElementById('category-name').value = '';
                    showAlert('Kategorie erfolgreich hinzugefügt', 'success');
                } catch (error) {
                    console.error('Fehler:', error);
                    showAlert(error.message, 'error');
                }
            });
        }

		// Mobile Menü Funktion
		function initMobileMenu() {
			const toggleBtn = document.getElementById('mobile-menu-toggle');
			const tabsContainer = document.querySelector('.tabs-container');
			
			toggleBtn.addEventListener('click', (e) => {
				e.stopPropagation();
				tabsContainer.classList.toggle('active');
				// Hintergrund-Overlay hinzufügen
				if (tabsContainer.classList.contains('active')) {
					const overlay = document.createElement('div');
					overlay.className = 'mobile-menu-overlay';
					overlay.addEventListener('click', () => {
						tabsContainer.classList.remove('active');
						overlay.remove();
					});
					document.body.appendChild(overlay);
				} else {
					document.querySelector('.mobile-menu-overlay')?.remove();
				}
			});
			
			// Menü schließen beim Klicken auf einen Tab
			document.querySelectorAll('.tab').forEach(tab => {
				tab.addEventListener('click', () => {
					tabsContainer.classList.remove('active');
					document.querySelector('.mobile-menu-overlay')?.remove();
				});
			});
		}

        // Hilfsfunktionen für UI
        function showLoader() {
            // Hier könnten Sie einen Ladeindikator einfügen
        }

        function hideLoader() {
            // Hier könnten Sie den Ladeindikator ausblenden
        }

        function showAlert(message, type = 'info') {
            const alertBox = document.createElement('div');
            alertBox.className = `alert alert-${type}`;
            alertBox.textContent = message;
            
            alertBox.style.position = 'fixed';
            alertBox.style.bottom = '80px';
            alertBox.style.left = '50%';
            alertBox.style.transform = 'translateX(-50%)';
            alertBox.style.padding = '12px 24px';
            alertBox.style.borderRadius = '8px';
            alertBox.style.backgroundColor = type === 'error' ? 'var(--danger)' : 
                                           type === 'success' ? 'var(--success)' : 'var(--primary)';
            alertBox.style.color = 'white';
            alertBox.style.boxShadow = 'var(--shadow-md)';
            alertBox.style.zIndex = '1000';
            alertBox.style.animation = 'fadeIn 0.3s ease-out';
            
            document.body.appendChild(alertBox);
            
            setTimeout(() => {
                alertBox.style.animation = 'fadeOut 0.3s ease-out';
                setTimeout(() => {
                    document.body.removeChild(alertBox);
                }, 300);
            }, 3000);
        }

        // Initialisierung
        function setCurrentDate() {
            const today = new Date();
            const dateStr = today.toISOString().split('T')[0];
            
            document.getElementById('expense-date').value = dateStr;
            document.getElementById('income-date').value = dateStr;
            document.getElementById('recurring-start-date').value = dateStr;
            document.getElementById('installment-start').value = dateStr;
            document.getElementById('current-month').value = today.toISOString().slice(0, 7);
        }

		document.addEventListener('DOMContentLoaded', function() {
			// Dark Mode Toggle
			const body = document.body;
			const btn = document.getElementById('darkToggle');
			
			// Prüfen auf gespeicherte Einstellung
			const isDark = localStorage.getItem('darkMode') === 'true';
			body.classList.toggle('dark-mode', isDark);
			btn.textContent = isDark ? "🌞 Hell" : "🌙 Dunkel";
			
			btn.addEventListener('click', () => {
				const isDark = body.classList.toggle('dark-mode');
				btn.textContent = isDark ? "🌞 Hell" : "🌙 Dunkel";
				localStorage.setItem('darkMode', isDark);
			});

			// Initialisierung der Komponenten
			initTabs();
			initMobileMenu();
			initForms();
			setCurrentDate();
			
			// Daten laden
			fetchData().catch(error => {
				console.error('Initialer Datenabruf fehlgeschlagen:', error);
				showAlert('Fehler beim Laden der Daten', 'error');
			});
		});
		
		function confirmDelete(itemId) {
		  if (confirm("Willst du diesen Eintrag wirklich löschen?")) {
			// ✅ Hier echte Löschlogik einfügen (z. B. AJAX oder Weiterleitung)
			console.log("Gelöscht:", itemId);

			// Beispiel mit Weiterleitung zu PHP-Skript
			window.location.href = `delete.php?id=${itemId}`;
		  }
		}
		
		function editItem(id) {
		  alert("Bearbeiten von Eintrag #" + id + " (hier kannst du z. B. ein Formular öffnen)");
		}
		
		function editCategory(id) {
			const category = finances.categories.find(cat => cat.id === id);
			if (!category) return;

			const newName = prompt("Neuer Kategoriename:", category.name);
			if (newName !== null && newName.trim() !== '') {
				const payload = {
					action: "update_category",
					id: id,
					name: newName.trim()
				};

				fetch('api.php', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify(payload)
				})
				.then(res => res.json())
				.then(data => {
					if (data.success) {
						category.name = newName.trim(); // lokal aktualisieren
						updateCategoriesTable();
						alert("Kategorie erfolgreich geändert!");
					} else {
						alert("Fehler beim Speichern: " + (data.error || "Unbekannter Fehler"));
					}
				})
				.catch(err => {
					console.error(err);
					alert("Netzwerkfehler.");
				});
			}
		}

    </script>
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<script>

document.addEventListener('DOMContentLoaded', function () {

	let finances = {
		settings: {},
		incomes: [],
		expenses: [],
		recurringExpenses: [],
		installments: [],
		categories: [],
		monthlyData: {}
	};

	fetch(`${API_URL}?action=get_data`, {
		credentials: 'include'
	})
	.then(response => response.json())
	.then(data => {
		if (data.status === 'success') {
			finances = data;
			updateUI();
			updateCategorySelects();
			renderMonthlyChart(data.monthlyData);
		} else {
			console.error('Fehler beim Laden der Daten:', data.error);
		}
	})
	.catch(error => {
		console.error('Netzwerkfehler:', error);
	});

	function renderMonthlyChart(monthlyData) {
	const now = new Date();

	// 3 Monate davor bis 1 Monat danach (insgesamt 5 Monate)
	const months = [];
	for (let offset = -3; offset <= 1; offset++) {
		const date = new Date(now.getFullYear(), now.getMonth() + offset);
		const year = date.getFullYear();
		const month = String(date.getMonth() + 1).padStart(2, '0');
		const key = `${year}-${month}`;
		months.push(key);
	}

	const incomeData = months.map(m => monthlyData[m]?.income ?? 0);
	const expenseData = months.map(m => monthlyData[m]?.expenses ?? 0);
	const balanceData = months.map((m, i) => incomeData[i] - expenseData[i]);

	const ctx = document.getElementById('monthlyChart').getContext('2d');

	new Chart(ctx, {
		type: 'line',
		data: {
			labels: months,
			datasets: [
				{
					label: 'Einnahmen',
					data: incomeData,
					borderColor: '#10b981',
					backgroundColor: 'rgba(16, 185, 129, 0.1)',
					fill: true,
					tension: 0.3
				},
				{
					label: 'Ausgaben',
					data: expenseData,
					borderColor: '#ef4444',
					backgroundColor: 'rgba(239, 68, 68, 0.1)',
					fill: true,
					tension: 0.3
				},
				{
					label: 'Saldo',
					data: balanceData,
					borderColor: '#3b82f6',
					backgroundColor: 'rgba(59, 130, 246, 0.1)',
					fill: true,
					tension: 0.3
				}
			]
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
				legend: {
					position: 'top'
				},
				tooltip: {
					mode: 'index',
					intersect: false
				}
			},
			interaction: {
				mode: 'nearest',
				axis: 'x',
				intersect: false
			},
			scales: {
				y: {
					beginAtZero: true,
					ticks: {
						callback: value => value + ' €'
					}
				}
			}
		}
	});
}

});



	</script>
</body>
</html>