<?php

namespace App\Enums;

/**
 * The different types of care visits.
 */
enum CareVisitType: string
{
    case DomesticCare = 'domestic_care';
    case PersonalCare = 'personal_care';
    case MealPrep = 'meal_prep';
    case Medication = 'medication';
}
