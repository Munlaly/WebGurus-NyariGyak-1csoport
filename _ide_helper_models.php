<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int|null $parent_id
 * @property string $name
 * @property int|null $default_shelf_life_days
 * @property int|null $default_calories_per_100
 * @property float|null $default_protein
 * @property float|null $default_fat
 * @property float|null $default_carbs
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDefaultCaloriesPer100($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDefaultCarbs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDefaultFat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDefaultProtein($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDefaultShelfLifeDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedAt($value)
 */
	class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $base_unit
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredients newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredients newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredients query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredients whereBaseUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredients whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredients whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredients whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredients whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredients whereUpdatedAt($value)
 */
	class Ingredients extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $recipe_id
 * @property string $scheduled_date
 * @property string $meal_type
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealPlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealPlan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealPlan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealPlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealPlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealPlan whereMealType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealPlan whereRecipeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealPlan whereScheduledDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealPlan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealPlan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealPlan whereUserId($value)
 */
	class MealPlan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $recipe_id
 * @property int $ingredient_id
 * @property float $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeIngredient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeIngredient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeIngredient query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeIngredient whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeIngredient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeIngredient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeIngredient whereIngredientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeIngredient whereRecipeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeIngredient whereUpdatedAt($value)
 */
	class RecipeIngredient extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string $name
 * @property string|null $instructions
 * @property int|null $prep_time_minutes
 * @property int $is_public
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipes newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipes newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipes query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipes whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipes whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipes whereInstructions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipes whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipes whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipes wherePrepTimeMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipes whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipes whereUserId($value)
 */
	class Recipes extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $diet_preference
 * @property int|null $daily_calorie_target
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDailyCalorieTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDietPreference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $ingredient_id
 * @property float|null $amount_left
 * @property string|null $status
 * @property string|null $expiration_date
 * @property int $is_frozen
 * @property string|null $last_audited_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInventory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInventory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInventory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInventory whereAmountLeft($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInventory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInventory whereExpirationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInventory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInventory whereIngredientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInventory whereIsFrozen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInventory whereLastAuditedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInventory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInventory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInventory whereUserId($value)
 */
	class UserInventory extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereUserId($value)
 */
	class UserSettings extends \Eloquent {}
}

