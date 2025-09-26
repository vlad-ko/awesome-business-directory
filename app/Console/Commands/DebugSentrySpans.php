<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DebugSentrySpans extends Command
{
    protected $signature = 'debug:sentry-spans';
    protected $description = 'Debug Sentry span creation';

    public function handle()
    {
        $this->info('Creating test spans for Sentry...');
        
        // Create a transaction with Sentry's native functions
        $transactionContext = new \Sentry\Tracing\TransactionContext();
        $transactionContext->setName('debug.test.transaction');
        $transactionContext->setOp('debug.test');
        
        $transaction = \Sentry\startTransaction($transactionContext);
        \Sentry\SentrySdk::getCurrentHub()->setSpan($transaction);
        
        // Create child spans with critical.* operations
        $span1 = $transaction->startChild(
            \Sentry\Tracing\SpanContext::make()
                ->setOp('critical.discovery.start')
                ->setDescription('Critical Discovery Start Test')
        );
        sleep(1);
        $span1->finish();
        
        $span2 = $transaction->startChild(
            \Sentry\Tracing\SpanContext::make()
                ->setOp('critical.discovery.view')
                ->setDescription('Critical Business View Test')
        );
        sleep(1);
        $span2->finish();
        
        $span3 = $transaction->startChild(
            \Sentry\Tracing\SpanContext::make()
                ->setOp('critical.onboarding.step_1')
                ->setDescription('Critical Onboarding Step 1')
        );
        sleep(1);
        $span3->finish();
        
        // Add tags via scope
        \Sentry\configureScope(function (\Sentry\State\Scope $scope) {
            $scope->setTag('test', 'true');
            $scope->setTag('debug', 'sentry-spans');
            $scope->setTag('critical.test', 'visibility');
        });
        
        // Add custom data to transaction
        if (method_exists($transaction, 'setData')) {
            $transaction->setData([
                'test_purpose' => 'span_visibility',
                'critical_tracking' => true
            ]);
        }
        
        // Finish transaction
        $transaction->finish();
        
        $this->info('✅ Test spans created!');
        $this->newLine();
        $this->info('🔍 Try these queries in Sentry Trace Explorer:');
        $this->info('1. transaction:debug.test.transaction');
        $this->info('2. debug:sentry-spans');
        $this->info('3. span.op:critical.discovery.start');
        $this->info('4. has:test');
        $this->newLine();
        $this->info('If spans still don\'t appear with span.op queries, try:');
        $this->info('- Click on the transaction ID to see child spans');
        $this->info('- Use Discover instead: event.type:span AND transaction:debug.test.transaction');
        
        return 0;
    }
}
