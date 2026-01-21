# 🚀 Guide d'Utilisation Rapide - Haga Hosting

## ✅ Statut du Système
- ✓ HTTPS configuré
- ✓ Apache2 actif
- ✓ PHP opérationnel
- ✓ Tous les fichiers en place
- ✓ Ports 80/443 ouverts

---

## 📍 Accès Rapides

### Pour les **CLIENTS** (Nouveaux et existants)
| Action | URL |
|--------|-----|
| **S'inscrire** | `https://192.168.88.163/web/register.php` |
| **Se connecter** | `https://192.168.88.163/web/client_index.php` |
| **Uploader des fichiers** | `https://192.168.88.163/web/client_dashboard.php` (une fois connecté) |

### Pour l'**ADMIN**
| Action | URL |
|--------|-----|
| **Se connecter** | `https://192.168.88.163/web/login.php` |
| **Dashboard** | `https://192.168.88.163/web/index.php` (une fois connecté) |
| **Créer un projet** | `https://192.168.88.163/web/create_project.php` (une fois connecté) |
| **Gérer les clients** | `https://192.168.88.163/web/manage_clients.php` (une fois connecté) |
| **Vérifier le statut** | `https://192.168.88.163/web/status.php` |

---

## 👤 Comptes de Test

### Admin
```
Utilisateur: admin
Mot de passe: hagasite
```

### Client Exemple
```
Nom: Jean Dupont
Email: jean@example.com
Téléphone: +33 1 23 45 67 89
Entreprise: ACME Corp
Mot de passe: test123
```

---

## 🎯 Workflow Complet

### 1️⃣ UN CLIENT S'INSCRIT
```
1. Va sur register.php
2. Remplit tous les champs
3. Clique sur "S'inscrire"
4. Compte créé automatiquement
→ Peut maintenant se connecter
```

### 2️⃣ LE CLIENT SE CONNECTE
```
1. Va sur client_index.php
2. Entre Email + Mot de passe
3. Clique sur "Se connecter"
→ Accès au dashboard
```

### 3️⃣ LE CLIENT UPLOAD SES FICHIERS
```
1. Sur le dashboard
2. Sélectionne un fichier (ZIP, TAR, PDF, etc.)
3. Clique sur "Uploader le fichier"
4. Fichier stocké et admin notifié
```

### 4️⃣ L'ADMIN GÈRE LE CLIENT
```
1. Admin va sur manage_clients.php
2. Voit tous les clients et leurs statuts
3. Clique sur "Éditer" pour un client
4. Remplit Domaine + Servername
5. Clique sur "Mettre à jour"
→ Client a maintenant son domaine
```

### 5️⃣ LE CLIENT ACCÈDE À SON SITE
```
Le client peut maintenant accéder à: https://[domaine]
```

---

## 🔑 Identifiants Admin par Défaut

**À CHANGER ABSOLUMENT EN PRODUCTION !**

Fichier: `web/config.php`
```php
define('ADMIN_USER', 'admin');      // À changer
define('ADMIN_PASS', 'hagasite');   // À changer
```

---

## 📁 Structure des Données

### Fichier clients: `web/donnee.txt`
Format (séparé par `|`):
```
Nom|Email|Téléphone|Entreprise|MotDePasse|DateCreation|Domaine|Servername
```

**Exemple:**
```
Jean Dupont|jean@example.com|+33123456789|ACME Corp|test123|2026-01-21 15:30:00|example.com|ns1.example.com
Marie Martin|marie@example.com|+33198765432|Tech|pass123|2026-01-21 16:00:00||
```

### Fichiers clients uploadés: `web/client_uploads/[HASH_EMAIL]/`
- Chaque client a son dossier personnel
- Les fichiers y sont stockés automatiquement

---

## 🛠️ Dépannage Rapide

### Le client ne peut pas se connecter
```
→ Vérifier que email + mot de passe sont corrects dans donnee.txt
→ Vérifier que le compte a bien été créé
```

### L'upload ne fonctionne pas
```
→ Vérifier les permissions: chmod 755 web/client_uploads/
→ Vérifier que l'extension du fichier est autorisée
→ Vérifier l'espace disque disponible
```

### Admin ne reçoit pas de notifications email
```
→ Modifier email_notification.php avec un vrai email
→ Vérifier que le serveur mail est configuré: sudo apt install mailutils
```

### Certificat SSL non reconnu
```
→ C'est NORMAL pour un certificat auto-signé
→ Cliquer sur "Accepter le risque" dans le navigateur
→ Pour un vrai certificat: utiliser Let's Encrypt
```

---

## 🔒 Sécurité - À Faire

- [ ] Modifier les identifiants admin
- [ ] Hasher les mots de passe clients (voir: password_hash)
- [ ] Ajouter une double authentification
- [ ] Mettre en place les logs d'accès
- [ ] Chiffrer les données sensibles
- [ ] Obtenir un certificat SSL valide (Let's Encrypt)

---

## 📊 Monitoring

### Vérifier les logs Apache
```bash
tail -f /var/log/apache2/error.log      # Erreurs
tail -f /var/log/apache2/access.log     # Accès
```

### Vérifier les logs MySQL
```bash
tail -f /var/log/mysql/error.log
```

### Vérifier l'espace disque
```bash
df -h                          # Disque
du -sh /var/www/               # Taille du web
```

---

## 🚀 Améliorations Possibles

1. **Base de données** - Remplacer donnee.txt par MySQL/PostgreSQL
2. **Authentification** - Ajouter OAuth2 / SSO
3. **Paiements** - Intégrer Stripe/PayPal
4. **API REST** - Exposer les fonctionnalités en API
5. **Dashboard avancé** - Ajouter des graphiques et statistiques
6. **Notifications** - SMS, Push notifications
7. **Backup automatique** - Sauvegardes journalières

---

## 📞 Support

**Documentation complète**: `web/README_CLIENT_SYSTEM.md`

**Pages utiles**:
- Statut système: `/web/status.php`
- Gestion des clients: `/web/manage_clients.php` (admin)
- Dashboard client: `/web/client_dashboard.php` (client connecté)

---

**Dernière mise à jour**: 21 janvier 2026  
**Version**: 1.0.0  
**Statut**: ✅ Production Ready (avec recommandations de sécurité)
