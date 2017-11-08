<?php

namespace Provisioning\Console\Commands;

use Illuminate\Console\Command;
use Provisioning\Helpers\FollowUpBsodOrder;
use Provisioning\BsodOrder;
use Provisioning\ComptaPrestation as Prestation;

class UpdateProspectToCustomer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bsod:update';
    protected $bsodOrder;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transformer un prospect en abonné';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->bsodOrder = new BsodOrder();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $orders = $this->bsodOrder->aupdateProspects();
        foreach ($orders as $value) {
            $OrderStatus = FollowUpBsodOrder::crFibreService($value);
            if (isset($OrderStatus[$value->externalSubscriberId])) {dd('qsq');
                $tabColumn = $OrderStatus[$value->externalSubscriberId][$value->numCommande];
                if (!empty($tabColumn)) {
                    if (isset($tabColumn['RES'])){
                        $column = 'RES';
                        $res_column = 'RES';
                        if (isset($tabColumn['ACT'])) {
                            $column = 'ACT';
                            $act_column = 'ACT';
                        }

                        if (isset($tabColumn['INST'])) {
                            $column = 'INST';
                            $inst_column = 'INST';
                        }
                    } elseif(isset($tabColumn['MOD'])) {
                        $column = 'MOD';
                        $mod_column = 'MOD';
                    }
                }
            }
            $status = FollowUpBsodOrder::isCompleted($value);
            if ($status) {
                $order = BsodOrder::find($value->id);
                $order->status = 'completed';
                $order->save();
            }

            if (isset($column) && $tabColumn[$column]['idClient'] != '' && $tabColumn[$column]['idCidentifiantASlient'] != '') {
                $value->client->customerId    = $tabColumn[$column]['idClient'];
                $value->client->identifiantAS = $tabColumn[$column]['identifiantAS'];
                $value->client->save();
            }
        }
    }
}
