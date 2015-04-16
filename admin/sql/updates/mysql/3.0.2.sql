# In version 3.0.2 J!1.6 ACL Viewing Access Levels are introduced, so an access field is needed
ALTER TABLE `#__rsgallery2_galleries` ADD `access` int(10) DEFAULT NULL;
# Set Viewing Access level to Public (1) for all galleries
UPDATE `#__rsgallery2_galleries` SET `access`=1;

# A user reported having item names cut off: increasing the length of some fields
ALTER TABLE `#__rsgallery2_files` MODIFY `name` varchar(255); 
ALTER TABLE `#__rsgallery2_files` MODIFY `title` varchar(255); 