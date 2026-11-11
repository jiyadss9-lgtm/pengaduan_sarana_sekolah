<?php
$theme = $_SESSION['theme'] ?? 'dark';
?>

<div class="page-container">

    <div class="topbar">

        <a href="javascript:history.back()"
           class="back-btn">

            ← Kembali

        </a>

        <div>

            <h2>
                Profil
            </h2>

            <p class="topbar-subtitle">
                Informasi akun pengguna
            </p>

        </div>

    </div>


    <div class="profile-card">

        <div class="profile-avatar">

            <?= strtoupper(substr($user['nama'], 0, 1)); ?>

        </div>


        <h1>
            <?= htmlspecialchars($user['nama']); ?>
        </h1>


        <p class="profile-role">

            <?= htmlspecialchars($user['role']); ?>

        </p>


        <div class="profile-info">

            <div class="info-item">

                <div>

                    <span>
                        Nama Lengkap
                    </span>

                    <strong>
                        <?= htmlspecialchars($user['nama']); ?>
                    </strong>

                </div>

                <div class="info-icon">
                    👤
                </div>

            </div>


            <div class="info-item">

                <div>

                    <span>
                        Username
                    </span>

                    <strong>
                        <?= htmlspecialchars($user['username']); ?>
                    </strong>

                </div>

                <div class="info-icon">
                    🔑
                </div>

            </div>


            <div class="info-item">

                <div>

                    <span>
                        Email
                    </span>

                    <strong>
                        <?= htmlspecialchars($user['email']); ?>
                    </strong>

                </div>

                <div class="info-icon">
                    ✉️
                </div>

            </div>


            <div class="info-item">

                <div>

                    <span>
                        Hak Akses
                    </span>

                    <strong>
                        <?= htmlspecialchars($user['role']); ?>
                    </strong>

                </div>

                <div class="info-icon">
                    🛡️
                </div>

            </div>

        </div>

    </div>

</div>