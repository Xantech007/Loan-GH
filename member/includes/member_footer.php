<!-- </div> -->
<!-- </div>  -->
<!-- End of content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="../script.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>

<!-- MY LONAS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#loanTable').DataTable();
            $('#repaymentTable').DataTable();
        });
    </script>


<!-- LOAN APPLICATION -->
<script>
        function showForm(formId) {
            document.querySelectorAll('.loan-form').forEach(form => form.classList.add('hidden'));
            document.getElementById(formId).classList.remove('hidden');
            document.querySelectorAll('.loan-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
        }
    </script>

<!-- NOTIFICATIONS -->
<!-- <script>
    function fetchNotifications() {
    fetch('../admin/member/fetch_notifications.php')
        .then(response => response.json())  // Fix: Removed semicolon here
        .then(data => {
            const list = document.getElementById('notificationList');
            const count = document.getElementById('notificationCount');
            
            list.innerHTML = '';
            count.textContent = data.length;

            data.forEach(notification => {
                const item = document.createElement('li');
                item.innerHTML = `${notification.title} - ${notification.message} 
                                  <button onclick="markAsRead(${notification.id})">Mark as Read</button>`;
                list.appendChild(item);
            });
        })
        .catch(error => {
            console.error('Error fetching notifications:', error);  // Added error handling
                });
        }

        function markAsRead(id) {
            fetch('../member/notifications/update_notification.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}`
            }).then(() => fetchNotifications())
            .catch(error => {
                console.error('Error marking notification as read:', error);  // Added error handling
            });
        }


        function toggleNotifications() {
            const panel = document.getElementById('notificationPanel');
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        }

        setInterval(fetchNotifications, 5000); // Refresh every 5 seconds
        window.onload = fetchNotifications;

 </script> -->


<!-- SIDE NAVIGATION -->
 <script>
 function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("overlay");

    sidebar.classList.toggle("active");

    // Only show overlay if screen width is less than 768px
    if (window.innerWidth < 768) {
        overlay.classList.toggle("active");
    }
}
 </script>
</body>
</html>

