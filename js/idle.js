let idleTime = 0;

            function resetIdleTime() {
                idleTime = 0;
            }

            window.addEventListener("mousemove", resetIdleTime);
            window.addEventListener("keypress", resetIdleTime);

            let modalActive = false;

            function isModalActive() {
                return modalActive;
            }

            function openModal() {
                modalActive = true;
                document.getElementById('myModal_lock').style.display = 'block';
            }

            function closeModal() {
                modalActive = false;
                document.getElementById('myModal_lock').style.display = 'none';
            }

            function beforeUnloadHandler(event) {
                if (isModalActive()) {
                    event.preventDefault();
                    event.returnValue = 'Are you sure you want to leave?';
                }
            }

            setInterval(function () {
                if (isModalActive()) {
                    window.addEventListener('beforeunload', beforeUnloadHandler);
                } else {
                    window.removeEventListener('beforeunload', beforeUnloadHandler);
                }
            }, 1000);

            function isAnyModalOpen() {
                const modals = document.querySelectorAll('.modal');
                for (const modal of modals) {
                    if (window.getComputedStyle(modal).display !== 'none') {
                        return true;
                    }
                }
                return false;
            }

            function checkIdleTime() {
                idleTime++;
                if (idleTime >= 360) { // 1 minute (in seconds)
                    if (!isAnyModalOpen()) {
                        openModal();
                    }
                }
            }

            document.getElementById('lock_form').addEventListener('submit', function(event) {
                event.preventDefault();
                let username = document.getElementById('username').value;
                let password = document.getElementById('password').value;
            $.ajax({
                url: '../authenticate.php',
                type: "POST",
                data: {
                    username,
                    password
                },
                success: function(response) {
                    if (response.status === 'success') {
                        document.getElementById('lock_form').reset();
                        closeModal();
                    } else {
                        alert('Invalid password. Please Try Again.');
                    }
                },
                error: function(response) {
                    console.error('Error authenticating:', error);
                }
            });

               


            });

            setInterval(checkIdleTime, 1000);