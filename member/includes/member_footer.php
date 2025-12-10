<?php // ./includes/member_footer.php ?>
    </div> <!-- End of .main -->

    <!-- Bootstrap 5 Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
            crossorigin="anonymous"></script>

    <!-- jQuery (only once) + DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" 
            integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" 
            crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <!-- Initialize DataTables -->
    <script>
        $(document).ready(function () {
            // Initialize all tables with these IDs
            $('#loanTable, #repaymentTable, #myLoansTable, #transactionsTable').DataTable({
                paging: true,
                searching: true,
                info: true,
                lengthChange: false,
                pageLength: 10,
                language: {
                    search: "Search records:",
                    paginate: {
                        previous: "Prev",
                        next: "Next"
                    }
                }
            });
        });
    </script>

    <!-- Loan Form Switcher -->
    <script>
        function showForm(formId) {
            // Hide all forms
            document.querySelectorAll('.loan-form').forEach(form => {
                form.classList.add('hidden');
            });
            // Show selected form
            document.getElementById(formId).classList.remove('hidden');

            // Update active button state
            document.querySelectorAll('.loan-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
        }
    </script>

    <!-- Sidebar Toggle - Works perfectly with your new header -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');

            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        // Auto-show sidebar on desktop (≥768px)
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 768) {
                document.getElementById('sidebar').classList.add('active');
                document.getElementById('overlay').classList.remove('active');
            }
        });

        // Optional: Close sidebar when clicking a link (mobile UX improvement)
        document.querySelectorAll('.menu a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 768) {
                    toggleSidebar();
                }
            });
        });
    </script>

    <!-- Your custom scripts (if any) -->
    <script src="../script.js"></script>
    <script src="./app.js"></script>

</body>
</html>
