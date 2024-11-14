IMAGES_PATH="$(dirname "$(realpath "$0")")/images"

# plugin must be active so that we're sure the VIP_customer role exists
wp plugin activate shipping-extras

# upload product images
COD_IMAGE_ID=$(wp media import "$IMAGES_PATH/cod.jpg" --porcelain)
SALMON_IMAGE_ID=$(wp media import "$IMAGES_PATH/salmon.jpg" --porcelain)
HALIBUT_IMAGE_ID=$(wp media import "$IMAGES_PATH/halibut.jpg" --porcelain)


# create products
wp wc product create --user=admin \
    --name="Salmon" \
    --regular_price=20 \
    --short_description="Fresh salmon" \
    --description="Beautiful fresh salmon" \
    --images="[{\"id\":$SALMON_IMAGE_ID}]"
wp wc product create --user=admin \
    --name="Halibut" \
    --regular_price=23 \
    --short_description="Fresh halibut" \
    --description="Beautiful fresh halibut" \
    --images="[{\"id\":$HALIBUT_IMAGE_ID}]"
wp wc product create --user=admin \
    --name="Cod" \
    --regular_price=25 \
    --short_description="Fresh cod" \
    --description="Beautiful fresh cod" \
    --images="[{\"id\":$COD_IMAGE_ID}]"

# create users
wp user create jdoe     jdoe@example.com     --role="customer"     --first_name="John"          --last_name="Doe"    --user_pass="jdoe"
wp user create viparker viparker@example.com --role="VIP_customer" --first_name="Victor Irving" --last_name="Parker" --user_pass="viparker"

# create "global" shipping zone
wp wc shipping_zone create --name="Global" --user=admin

# create shipping rates
wp eval-file "$(dirname "$(realpath "$0")")/create_shipping_rates.php"
