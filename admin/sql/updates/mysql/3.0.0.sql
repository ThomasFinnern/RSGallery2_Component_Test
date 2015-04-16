# In version 3.0.0 J!1.6 ACL is introduced, so an asset_id field is needed

ALTER TABLE `#__rsgallery2_galleries` ADD `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.';
ALTER TABLE `#__rsgallery2_files` ADD `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.';