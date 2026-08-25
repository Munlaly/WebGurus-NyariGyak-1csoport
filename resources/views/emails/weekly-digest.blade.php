<x-mail::message>
# Hello! 👋

Here is your weekly summary of your impact with **Smart & ZeroWaste Menu Planner**. 

<x-mail::panel>
### Your Weekly Stats:
* **Meals Planned:** {{ $stats['mealsCount'] }} meals
* **Estimated Food Saved:** {{ $stats['wasteSavedKg'] }} kg
* **Shared Ingredients Reused:** {{ $stats['activeIngredientsUsed'] }} items
</x-mail::panel>

Great job staying organized and minimizing food waste this week! You can check out your updated meal plan for the upcoming week below.

<x-mail::button :url="route('dashboard')">
View Your Dashboard
</x-mail::button>

Thanks,<br>
The **Smart & ZeroWaste** Team
</x-mail::message>