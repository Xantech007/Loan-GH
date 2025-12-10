<?php // ./includes/member_footer.php ?>
    </div> <!-- End of .main -->

    <!-- Bootstrap + Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#loanTable, #repaymentTable').DataTable();
        });

        function showForm(formId) {
            document.querySelectorAll('.loan-form').forEach(f => f.classList.add('hidden'));
            document.getElementById(formId).classList.remove('hidden');
            document.querySelectorAll('.loan-btn').forEach(b => b.classList.remove('active'));
            event.target.classList.add('active');
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('hidden');
            overlay.classList.toggle('active');
        }

        // Ensure sidebar is visible on desktop
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                document.getElementById('sidebar').classList.remove('hidden');
                document.getElementById('overlay').classList.remove('active');
            }
        });
    </script>
</body>
</html>
