find ./ -type f -exec sed -i -e 's|Pray4Movement_Prayer_Points|Pray4Movement_Prayer_Points|g' {} \;
find ./ -type f -exec sed -i -e 's|pray4movement_prayer_points|pray4movement_prayer_points|g' {} \;
find ./ -type f -exec sed -i -e 's|pray4movement-prayer-points|pray4movement-prayer-points|g' {} \;
find ./ -type f -exec sed -i -e 's|prayer_point|prayer_point|g' {} \;
find ./ -type f -exec sed -i -e 's|Pray4Movement Prayer Points|Pray4Movement Prayer Points|g' {} \;
mv pray4movement-prayer-points.php pray4movement-prayer-points.php
rm .rename.sh
