<?php
require_once '../includes/config.php';
requireAdminLogin();
// if (!hasPermission('messages_manage')) { /* Yetki kontrolü eklenebilir */ }

$pageTitle = 'Gelen Mesajlar';

$success = $_SESSION['success_message'] ?? '';
$error = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

$action = $_GET['action'] ?? 'list';
$message_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Mesaj silme işlemi
if ($action === 'delete' && $message_id > 0) {
    $csrf_token = $_GET['csrf_token'] ?? '';
    if (verifyCSRFToken($csrf_token)) {
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
        if ($stmt->execute([$message_id])) {
            $_SESSION['success_message'] = 'Mesaj başarıyla silindi.';
        } else {
            $_SESSION['error_message'] = 'Mesaj silinirken bir hata oluştu.';
        }
    } else {
        $_SESSION['error_message'] = 'Güvenlik hatası. Silme işlemi gerçekleştirilemedi.';
    }
    header('Location: messages.php');
    exit;
}

// Mesajı okundu/okunmadı olarak işaretleme
if ($action === 'toggle_read' && $message_id > 0) {
    $csrf_token = $_GET['csrf_token'] ?? '';
     if (verifyCSRFToken($csrf_token)) {
        $current_status = fetchOne("SELECT is_read FROM contact_messages WHERE id = ?", [$message_id]);
        if ($current_status) {
            $new_status = $current_status['is_read'] ? 0 : 1;
            $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = ?, updated_at = NOW() WHERE id = ?");
            if ($stmt->execute([$new_status, $message_id])) {
                $_SESSION['success_message'] = $new_status ? 'Mesaj okundu olarak işaretlendi.' : 'Mesaj okunmadı olarak işaretlendi.';
            } else {
                $_SESSION['error_message'] = 'Durum güncellenirken bir hata oluştu.';
            }
        } else {
             $_SESSION['error_message'] = 'Mesaj bulunamadı.';
        }
    } else {
        $_SESSION['error_message'] = 'Güvenlik hatası. İşlem gerçekleştirilemedi.';
    }
    header('Location: messages.php' . (isset($_GET['view_id']) ? '?action=view&id=' . $_GET['view_id'] : ''));
    exit;
}


$messages = [];
$message_detail = null;

if ($action === 'view' && $message_id > 0) {
    $message_detail = fetchOne("SELECT * FROM contact_messages WHERE id = ?", [$message_id]);
    if ($message_detail) {
        if ($message_detail['is_read'] == 0) {
            // Otomatik okundu olarak işaretle
            query("UPDATE contact_messages SET is_read = 1, updated_at = NOW() WHERE id = ?", [$message_id]);
            $message_detail['is_read'] = 1; // Detay görünümünde hemen yansıt
        }
    } else {
        $error = "Mesaj bulunamadı.";
        $action = 'list'; // Listeye geri dön
    }
}

if ($action === 'list') {
    // Sayfalama için hazırlık
    $perPage = 15;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $perPage;

    $totalMessages = fetchOne("SELECT COUNT(*) as count FROM contact_messages")['count'];
    $totalPages = ceil($totalMessages / $perPage);

    $messages = fetchAll("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT :limit OFFSET :offset", [
        ':limit' => $perPage,
        ':offset' => $offset
    ]);
}


include 'includes/header.php';
?>

<?php if ($success): ?>
<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md" role="alert">
    <p class="font-bold">Başarılı!</p>
    <p><?php echo htmlspecialchars($success); ?></p>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md" role="alert">
    <p class="font-bold">Hata!</p>
    <p><?php echo htmlspecialchars($error); ?></p>
</div>
<?php endif; ?>


<?php if ($action === 'view' && $message_detail): ?>
<div class="bg-white shadow-md rounded-lg p-6 mb-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold text-gray-800">Mesaj Detayı</h2>
        <a href="messages.php" class="text-red-600 hover:text-red-800 transition duration-150 ease-in-out">
            <i class="fas fa-arrow-left mr-2"></i>Tüm Mesajlara Dön
        </a>
    </div>
    <div class="border-t border-gray-200 pt-4">
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
            <div class="col-span-1">
                <dt class="text-sm font-medium text-gray-500">Gönderen Ad Soyad</dt>
                <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($message_detail['name']); ?></dd>
            </div>
            <div class="col-span-1">
                <dt class="text-sm font-medium text-gray-500">E-posta</dt>
                <dd class="mt-1 text-sm text-gray-900"><a href="mailto:<?php echo htmlspecialchars($message_detail['email']); ?>" class="text-red-600 hover:underline"><?php echo htmlspecialchars($message_detail['email']); ?></a></dd>
            </div>
            <?php if ($message_detail['phone']): ?>
            <div class="col-span-1">
                <dt class="text-sm font-medium text-gray-500">Telefon</dt>
                <dd class="mt-1 text-sm text-gray-900"><a href="tel:<?php echo htmlspecialchars($message_detail['phone']); ?>" class="text-red-600 hover:underline"><?php echo htmlspecialchars($message_detail['phone']); ?></a></dd>
            </div>
            <?php endif; ?>
            <?php if ($message_detail['company']): ?>
            <div class="col-span-1">
                <dt class="text-sm font-medium text-gray-500">Şirket</dt>
                <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($message_detail['company']); ?></dd>
            </div>
            <?php endif; ?>
            <div class="col-span-1">
                <dt class="text-sm font-medium text-gray-500">Konu</dt>
                <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($message_detail['subject'] ?: 'Belirtilmemiş'); ?></dd>
            </div>
            <?php if ($message_detail['product_info']): ?>
            <div class="col-span-1">
                <dt class="text-sm font-medium text-gray-500">İlgili Ürün</dt>
                <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($message_detail['product_info']); ?></dd>
            </div>
            <?php endif; ?>
            <div class="col-span-1">
                <dt class="text-sm font-medium text-gray-500">Gönderim Tarihi</dt>
                <dd class="mt-1 text-sm text-gray-900"><?php echo date('d.m.Y H:i', strtotime($message_detail['created_at'])); ?></dd>
            </div>
             <div class="col-span-1">
                <dt class="text-sm font-medium text-gray-500">Okunma Durumu</dt>
                <dd class="mt-1 text-sm">
                    <?php if ($message_detail['is_read']): ?>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Okundu</span>
                    <?php else: ?>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Okunmadı</span>
                    <?php endif; ?>
                     <a href="messages.php?action=toggle_read&id=<?php echo $message_detail['id']; ?>&view_id=<?php echo $message_detail['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" 
                        class="ml-2 text-xs text-blue-600 hover:underline">
                        (<?php echo $message_detail['is_read'] ? 'Okunmadı yap' : 'Okundu yap'; ?>)
                     </a>
                </dd>
            </div>
            <div class="col-span-1 md:col-span-2">
                <dt class="text-sm font-medium text-gray-500">IP Adresi</dt>
                <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($message_detail['ip_address']); ?></dd>
            </div>
            <div class="col-span-1 md:col-span-2">
                <dt class="text-sm font-medium text-gray-500">Mesaj İçeriği</dt>
                <dd class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded-md whitespace-pre-wrap"><?php echo nl2br(htmlspecialchars($message_detail['message'])); ?></dd>
            </div>
        </dl>
    </div>
    <div class="mt-6 flex justify-end">
        <a href="messages.php?action=delete&id=<?php echo $message_detail['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" 
           onclick="return confirm('Bu mesajı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.');"
           class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition duration-150 ease-in-out">
           <i class="fas fa-trash-alt mr-2"></i>Mesajı Sil
        </a>
    </div>
</div>

<?php elseif ($action === 'list'): ?>
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-800">Gelen Mesajlar (<?php echo $totalMessages; ?>)</h2>
    </div>
    <?php if (empty($messages)): ?>
        <p class="text-center text-gray-500 py-10">Henüz gelen mesaj bulunmuyor.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gönderen</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Konu</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($messages as $msg): ?>
                    <tr class="<?php echo $msg['is_read'] ? 'bg-white' : 'bg-red-50 font-semibold'; ?>">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="messages.php?action=toggle_read&id=<?php echo $msg['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" 
                               title="<?php echo $msg['is_read'] ? 'Okunmadı olarak işaretle' : 'Okundu olarak işaretle'; ?>">
                                <?php if ($msg['is_read']): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Okundu</span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Yeni</span>
                                <?php endif; ?>
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($msg['name']); ?></div>
                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars($msg['email']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <?php echo htmlspecialchars(mb_substr($msg['subject'] ?: ($msg['product_info'] ?: 'Konu Yok'), 0, 30)); ?>
                            <?php if(mb_strlen($msg['subject'] ?: ($msg['product_info'] ?: 'Konu Yok')) > 30) echo '...'; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('d.m.Y H:i', strtotime($msg['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="messages.php?action=view&id=<?php echo $msg['id']; ?>" class="text-red-600 hover:text-red-900 mr-3" title="Görüntüle"><i class="fas fa-eye"></i></a>
                            <a href="messages.php?action=delete&id=<?php echo $msg['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" 
                               onclick="return confirm('Bu mesajı silmek istediğinizden emin misiniz?');" 
                               class="text-gray-500 hover:text-red-700" title="Sil"><i class="fas fa-trash-alt"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($totalPages > 1): ?>
        <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
            <nav class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Toplam <?php echo $totalMessages; ?> mesaj
                </div>
                <div class="flex-1 flex justify-end">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Önceki
                        </a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Sonraki
                        </a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>