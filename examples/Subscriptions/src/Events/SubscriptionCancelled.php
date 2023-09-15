<?php

namespace Thunk\Verbs\Examples\Subscriptions\Events;

use Thunk\Verbs\Event;
use Thunk\Verbs\Examples\Subscriptions\Models\Subscription;
use Thunk\Verbs\Examples\Subscriptions\States\GlobalReportState;
use Thunk\Verbs\Examples\Subscriptions\States\PlanReportState;
use Thunk\Verbs\Examples\Subscriptions\States\SubscriptionState;

class SubscriptionCancelled extends Event
{
    public SubscriptionState $subscription_state;

    public GlobalReportState $global_report_state;

    public PlanReportState $plan_report_state;

    public function __construct(
        public int $subscription_id,
    ) {
        $this->subscription_state = SubscriptionState::load($subscription_id);
        $this->plan_report_state = PlanReportState::load($this->subscription_state->plan_id);
        $this->global_report_state = GlobalReportState::singleton();
    }

    public function validate(SubscriptionState $state)
    {
        return $state->is_active;
    }

    public function apply(SubscriptionState $state)
    {
        $state->is_active = false;
    }

    public function applyToReport(PlanReportState $state)
    {
        $state->unsubscribes_since_last_report++;
    }

    public function onFire()
    {
        $subscription = Subscription::find($this->subscription_id);

        $subscription->is_active = false;
        $subscription->cancelled_at = now();
        $subscription->save();

//        $subscription->update([
//            'is_active' => false,
//            'cancelled_at' => now(),
//        ]);
    }
}
