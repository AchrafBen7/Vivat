<?php

namespace App\Http\Controllers\Web;

/**
 * Wrapper de compatibilité.
 *
 * Les routes publiques sont maintenant réparties entre :
 * - ContributorDashboardController
 * - ContributorSubmissionController
 * - ContributorProfileController
 */
class ContributorController extends ContributorSubmissionController
{
}
