<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Julian Day Calendar</title>
        <style>
            /* CSS styling would go here */
            body { font-family: sans-serif; }
            #calendar-grid {
                display: grid;
                grid-template-columns: auto repeat(31, 1fr); /* 31 days + 1 for month labels */
                gap: 1px;
                border: 1px solid #ccc;
            }
            .header-cell, .grid-cell {
                padding: 5px;
                text-align: center;
                background-color: #f0f0f0;
                border: 1px solid #eee;
            }
            .month-header { background-color: #e0e0e0; font-weight: bold; }
            .day-header { background-color: #d0d0d0; }
        </style>
    </head>
    <body>
        <h1>Julian Day of Year Calendar</h1>
        <div id="calendar-grid">
            <div class="header-cell"></div> <!-- Empty top-left corner -->
            <!-- Day headers (1-31) will be injected here by JavaScript -->
        </div>
    
        <script>
            // JavaScript for generating the grid and calculating Julian Day
            const calendarGrid = document.getElementById('calendar-grid');
    
            // Create day headers (1-31)
            for (let i = 1; i <= 31; i++) {
                const dayHeader = document.createElement('div');
                dayHeader.classList.add('header-cell', 'day-header');
                dayHeader.textContent = i;
                calendarGrid.appendChild(dayHeader);
            }
    
            const months = [
                "January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"
            ];
    
            // Generate rows for each month
            months.forEach((monthName, monthIndex) => {
                const monthHeader = document.createElement('div');
                monthHeader.classList.add('header-cell', 'month-header');
                monthHeader.textContent = monthName;
                calendarGrid.appendChild(monthHeader);
    
                for (let day = 1; day <= 31; day++) {
                    const gridCell = document.createElement('div');
                    gridCell.classList.add('grid-cell');
    
                    // Calculate Julian Day of Year (1-365 or 1-366 for leap years)
                    // This is a simplified calculation; real Julian day calculation is more complex
                    // For this example, we're calculating day of year within Gregorian calendar
                    const date = new Date(2025, monthIndex, day); // Use a specific year, e.g., 2025
                    const startOfYear = new Date(2025, 0, 1);
                    const diff = date.getTime() - startOfYear.getTime();
                    const oneDay = 1000 * 60 * 60 * 24;
                    const julianDayOfYear = Math.floor(diff / oneDay) + 1;
    
                    // Handle invalid dates (e.g., Feb 30th)
                    if (date.getMonth() === monthIndex && date.getDate() === day) {
                        gridCell.textContent = julianDayOfYear;
                    } else {
                        gridCell.textContent = ''; // Empty for invalid dates
                        gridCell.style.backgroundColor = '#ddd'; // Grey out invalid days
                    }
                    calendarGrid.appendChild(gridCell);
                }
            });
        </script>
    </body>
    </html>
    <div id="calendar-grid">
        <div class="header-cell"></div> <!-- Empty top-left corner -->
        <!-- Day headers (1-31) will be injected here by JavaScript -->
    </div>

    <script>
        // JavaScript for generating the grid and calculating Julian Day
        const calendarGrid = document.getElementById('calendar-grid');

        // Create day headers (1-31)
        for (let i = 1; i <= 31; i++) {
            const dayHeader = document.createElement('div');
            dayHeader.classList.add('header-cell', 'day-header');
            dayHeader.textContent = i;
            calendarGrid.appendChild(dayHeader);
        }

        const months = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];

        // Generate rows for each month
        months.forEach((monthName, monthIndex) => {
            const monthHeader = document.createElement('div');
            monthHeader.classList.add('header-cell', 'month-header');
            monthHeader.textContent = monthName;
            calendarGrid.appendChild(monthHeader);

            for (let day = 1; day <= 31; day++) {
                const gridCell = document.createElement('div');
                gridCell.classList.add('grid-cell');

                // Calculate Julian Day of Year (1-365 or 1-366 for leap years)
                // This is a simplified calculation; real Julian day calculation is more complex
                // For this example, we're calculating day of year within Gregorian calendar
                const date = new Date(2025, monthIndex, day); // Use a specific year, e.g., 2025
                const startOfYear = new Date(2025, 0, 1);
                const diff = date.getTime() - startOfYear.getTime();
                const oneDay = 1000 * 60 * 60 * 24;
                const julianDayOfYear = Math.floor(diff / oneDay) + 1;

                // Handle invalid dates (e.g., Feb 30th)
                if (date.getMonth() === monthIndex && date.getDate() === day) {
                    gridCell.textContent = julianDayOfYear;
                } else {
                    gridCell.textContent = ''; // Empty for invalid dates
                    gridCell.style.backgroundColor = '#ddd'; // Grey out invalid days
                }
                calendarGrid.appendChild(gridCell);
            }
        });
    </script>
</body>
</html>
