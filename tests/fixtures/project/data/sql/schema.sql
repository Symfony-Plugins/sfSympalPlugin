CREATE TABLE sf_guard_group (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(255) UNIQUE, description VARCHAR(1000), created_at DATETIME, updated_at DATETIME);
CREATE TABLE sf_guard_group_permission (group_id INTEGER, permission_id INTEGER, created_at DATETIME, updated_at DATETIME, PRIMARY KEY(group_id, permission_id));
CREATE TABLE sf_guard_permission (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(255) UNIQUE, description VARCHAR(1000), created_at DATETIME, updated_at DATETIME);
CREATE TABLE sf_guard_remember_key (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, remember_key VARCHAR(32), ip_address VARCHAR(50), created_at DATETIME, updated_at DATETIME);
CREATE TABLE sf_guard_user (id INTEGER PRIMARY KEY AUTOINCREMENT, username VARCHAR(128) NOT NULL UNIQUE, algorithm VARCHAR(128) DEFAULT 'sha1' NOT NULL, salt VARCHAR(128), password VARCHAR(128), is_active INTEGER DEFAULT '1', is_super_admin INTEGER DEFAULT '0', last_login DATETIME, created_at DATETIME, updated_at DATETIME);
CREATE TABLE sf_guard_user_group (user_id INTEGER, group_id INTEGER, created_at DATETIME, updated_at DATETIME, PRIMARY KEY(user_id, group_id));
CREATE TABLE sf_guard_user_permission (user_id INTEGER, permission_id INTEGER, created_at DATETIME, updated_at DATETIME, PRIMARY KEY(user_id, permission_id));
CREATE TABLE blog_post (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(255), body LONGTEXT, entity_id INTEGER);
CREATE TABLE comment (id INTEGER PRIMARY KEY AUTOINCREMENT, status VARCHAR(255) DEFAULT 'Pending' NOT NULL, user_id INTEGER, name VARCHAR(255), subject VARCHAR(255), body LONGTEXT NOT NULL, created_at DATETIME, updated_at DATETIME);
CREATE TABLE menu_item_translation (id INTEGER, label VARCHAR(255), lang CHAR(2), PRIMARY KEY(id, lang));
CREATE TABLE menu_item (id INTEGER PRIMARY KEY AUTOINCREMENT, site_id INTEGER NOT NULL, entity_type_id INTEGER, entity_id INTEGER, name VARCHAR(255) NOT NULL, route VARCHAR(255), has_many_entities INTEGER DEFAULT '0', requires_auth INTEGER, requires_no_auth INTEGER, is_primary INTEGER, is_published INTEGER, date_published DATETIME, root_id INTEGER, lft INTEGER, rgt INTEGER, level INTEGER);
CREATE TABLE menu_item_group (menu_item_id INTEGER, group_id INTEGER, PRIMARY KEY(menu_item_id, group_id));
CREATE TABLE menu_item_permission (menu_item_id INTEGER, permission_id INTEGER, PRIMARY KEY(menu_item_id, permission_id));
CREATE TABLE page (id INTEGER PRIMARY KEY AUTOINCREMENT, entity_id INTEGER, title VARCHAR(255) NOT NULL, disable_comments INTEGER);
CREATE TABLE entity (id INTEGER PRIMARY KEY AUTOINCREMENT, site_id INTEGER NOT NULL, entity_type_id INTEGER NOT NULL, entity_template_id INTEGER, master_menu_item_id INTEGER, last_updated_by INTEGER, created_by INTEGER, locked_by INTEGER, is_published INTEGER, date_published DATETIME, custom_path VARCHAR(255), layout VARCHAR(255), slug VARCHAR(255), created_at DATETIME, updated_at DATETIME);
CREATE TABLE entity_comment (entity_id INTEGER, comment_id INTEGER, PRIMARY KEY(entity_id, comment_id));
CREATE TABLE entity_group (entity_id INTEGER, group_id INTEGER, PRIMARY KEY(entity_id, group_id));
CREATE TABLE entity_permission (entity_id INTEGER, permission_id INTEGER, PRIMARY KEY(entity_id, permission_id));
CREATE TABLE entity_slot_translation (id INTEGER, value LONGTEXT, lang CHAR(2), PRIMARY KEY(id, lang));
CREATE TABLE entity_slot (id INTEGER PRIMARY KEY AUTOINCREMENT, entity_id INTEGER NOT NULL, entity_slot_type_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL);
CREATE TABLE entity_slot_type (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(255) NOT NULL);
CREATE TABLE entity_template (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(255) NOT NULL, type VARCHAR(255), entity_type_id INTEGER, partial_path VARCHAR(255), component_path VARCHAR(255), body LONGTEXT);
CREATE TABLE entity_type (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(255) NOT NULL, label VARCHAR(255) NOT NULL, list_route_url VARCHAR(255), view_route_url VARCHAR(255), layout VARCHAR(255), slug VARCHAR(255));
CREATE TABLE site (id INTEGER PRIMARY KEY AUTOINCREMENT, layout VARCHAR(255), title VARCHAR(255), description LONGTEXT, slug VARCHAR(255));
CREATE TABLE user_profile (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, first_name VARCHAR(255), last_name VARCHAR(255), email_address VARCHAR(255), entity_id INTEGER);
CREATE INDEX is_active_idx_idx ON sf_guard_user (is_active);
CREATE INDEX sluggable_idx ON entity_type (slug);
CREATE INDEX sluggable_idx ON site (slug);
