<?php

namespace Provisioning;

use Illuminate\Database\Eloquent\Model;
use Provisioning\Helpers\Auth\AuthUser;
use Provisioning\Helpers\LibTicket;
use Provisioning\Exceptions\MissingCentileContextException;
use Provisioning\Centile\Enterprise;
use Provisioning\Centile\PBXTrunking;
use Provisioning\CentilePrestationTypes;
use Provisioning\Events\PrestationTerminated;
use DB;

class ComptaPrestation extends Model
{
    protected $table = 'comptaPrestation';
    protected $fillable = [
        'clientID',
        'groupID',
        'type',
        'supplier',
        'valeur',
        'description',
        'validFrom',
        'validTill',
        'status',
        'linkedWith',
    ];
    protected $primaryKey = 'prestationID';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';
    const SUPPLIER_CENTILE = 11;
    const STATUS_COMPLETION = 255;

    public function getResellerID() {
    }

    public function getId()
    {
        return $this->{$this->primaryKey};
    }

    public function getType()
    {
        return $this->type;
    }

    public function getClientId()
    {
        return $this->clientID;
    }

    public function getGroupId()
    {
        return $this->groupID;
    }

    public function getSubscriptionDate()
    {
        return $this->validFrom;
    }

    public function getTerminationDate()
    {
        return $this->validTill;
    }

    public function isTerminated()
    {
        return $this->validTill !== null;
    }

    public function getComment()
    {
        return $this->commententaire;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getValue()
    {
        return $this->valeur;
    }

    public function produit()
    {
        return $this->hasMany('Provisioning\ComptaProduit');
    }

    public function references()
    {
        return $this->hasMany('Provisioning\PrestationReferences');
    }

    public function historyAdmin()
    {
        return $this->hasMany('Provisioning\HistoryAdmin');
    }

    public function linkedWith()
    {
        return $this->belongsTo(self::class, 'linkedWith')->first();
    }

    public function sibling()
    {
        return $this->hasMany(self::class, 'linkedWith');
    }

    public function getRouteKeyName()
    {
        return 'prestationID';
    }

    /**
     * @return string
     */
    public function nameForType()
    {
        switch ($this->type) {
            case 'BM':
                return trans('prestation/name.type.bm');
            case 'Domain':
                return trans('prestation/name.type.domain');
            case 'Hosting':
                return trans('prestation/name.type.hosting');
            case 'MX':
                return trans('prestation/name.type.mx');
            case 'CollecteDSL':
                return trans('prestation/name.type.collecte_dsl');
            case 'RadiusDSL':
                return trans('prestation/name.type.radius_dsl');
            case 'RadiusRTC':
                return trans('prestation/name.type.radius_rtc');
            case 'NS':
                return trans('prestation/name.type.ns');
            case 'Switchless':
                return trans('prestation/name.type.switchless');
            case 'VoIP':
                return trans('prestation/name.type.voip');
            case 'FILTRAGE_MAIL':
                return trans('prestation/name.type.filtrage_mail');
            case 'LAN':
                return trans('prestation/name.type.lan');
            case 'Liaison':
                return trans('prestation/name.type.liaison');
            case 'NetBlock':
                return trans('prestation/name.type.net_block');
            case 'TELEMAINT':
                return trans('prestation/name.type.telemaint');
            case 'Location':
                return trans('prestation/name.type.location');
            case 'LOCACHAT':
                return trans('prestation/name.type.locachat');
            case 'Achat':
                return trans('prestation/name.type.achat');
            case 'HOSTING_1U':
                return trans('prestation/name.type.hosting_1u');
            case 'HOSTING_DED':
                return trans('prestation/name.type.hosting_ded');
            case 'DOMBM':
                return trans('prestation/name.type.dombm');
            case 'Modem':
                return trans('prestation/name.type.modem');
            case 'PuceGSM':
                return trans('prestation/name.type.puce_gsm');
            case 'Bandwidth':
                return trans('prestation/name.type.bandwidth');
            case 'VGAST':
                return trans('prestation/name.type.vgast');
            case 'YellowP':
                return trans('prestation/name.type.yellowp');
            case 'Backup':
                return trans('prestation/name.type.backup');
            case 'Pack':
                return trans('prestation/name.type.pack');
            case 'FLLU':
                return trans('prestation/name.type.fllu');
            case 'Database':
                return trans('prestation/name.type.database');
            case 'TrancheSDA':
                return trans('prestation/name.type.tranche_sda');
            case 'EFM':
                return trans('prestation/name.type.efm');
        }
    }

    public function status()
    {
        switch ($this->status) {
            case 0:
                return trans('prestation/name.status.new');
            case 1:
                return trans('prestation/name.status.accepted');
            case 2:
                return trans('prestation/name.status.rejected');
            case 255:
                return trans('prestation/name.status.completed');
            default:
                return '';
        }
    }

    /**
     * @return mixed|string
     */
    public function caption()
    {
        switch ($this->type) {
        case 'BM':
        case 'Domain':
        case 'Hosting':
        case 'MX':
        case 'CollecteDSL':
        case 'RadiusDSL':
        case 'RadiusRTC':
        case 'NS':
        case 'Switchless':
        case 'VoIP':
        case 'VGAST':
        case 'YellowP':
        case 'FLLU':
        case 'Database':
          return $this->valeur;
        case 'FILTRAGE_MAIL':
          return trans('prestation/name.mail_filter');
        case 'DOMBM':
          return trans('prestation/name.mobile_office_pack');
        case 'LAN':
          return trans('prestation/name.ethernet_link');
        case 'Liaison':
          return trans('prestation/name.connection');
        case 'NetBlock':
          return $this->valeur;
        case 'TELEMAINT':
          return trans('prestation/name.maintenance');
        case 'Location':
          return trans('prestation/name.hire');
        case 'LOCACHAT':
          return trans('prestation/name.hire_purchase');
        case 'Achat':
          return trans('prestation/name.purchase');
        case 'HOSTING_1U':
          return trans('prestation/name.hosting_1u');
        case 'HOSTING_DED':
          return trans('prestation/name.hosting_ded');
        case 'Bandwidth':
          return trans('prestation/name.bandwidth');
        case 'Backup':
          return trans('prestation/name.backup');
        case 'Pack':
          return trans('prestation/name.pack', ['presta' => $this->valeur]);
        case 'Modem':
          return $this->valeur;
        case 'PuceGSM':
          return $this->valeur;
        case 'TrancheSDA':
          return $this->valeur;
        case 'EFM':
          return trans('prestation/name.efm');
        default:
          return '';
      }
    }

    /**
     * @return array
     */
    public function connected()
    {
        $products = $this->sibling()->orderBy('type')->get();
        $by_type = [];
        foreach ($products as $product) {
            if (!array_key_exists($product->type, $by_type)) {
                $by_type[$product->type] = ['title' => $product->nameForType(), 'products' => []];
            }
            array_push($by_type[$product->type]['products'], [
                'link' => route('prestation_show', ['id' => $product->prestationID]),
                'icon' => $product->icon(),
                'caption' => htmlspecialchars($product->caption()),
            ]);
        }

        return $by_type;
    }

    /**
     * @return array
     */
    public function ticket()
    {
        $tickets = [];
        $ticket_open = LibTicket::getTicket($this->prestationID);
        if (!empty($ticket_open)) {
            foreach ($ticket_open as $ticket) {
                $date = new \DateTime($ticket['created']);
                $v['U_TICKET'] = $ticket['url'];
                $v['T_TCAPTION'] = htmlspecialchars($ticket['title']);
                $v['T_TDATE'] = htmlspecialchars(strftime('%A, %e %B %Y à %H:%M', $date->getTimestamp()));
                $v['T_TSTATUS'] = htmlspecialchars($ticket['status_txt']);
                array_push($tickets, $v);
            }
        }

        return $tickets;
    }

    public function url($token)
    {
        return str_replace(['__PRESTAID__'], [$this->prestationID], env($token));
    }

    /**
     * @param      $action
     *                      $action = "C" create or "U" update or "D" delete
     * @param null $comment
     */
    public function saveHistory($action, $comment = null)
    {
        $history = new HistoryAdmin();
        $history->userName = AuthUser::getUserID();
        $history->action = $action;
        $history->prestaID = $this->prestationID;
        $history->timestamp = new \DateTime();
        $history->comment = $comment;
        $history->save();
    }

    public function getCentileContext()
    {
        if (!$context = $this->centileContext)
            throw new MissingCentileContextException('Prestation ' . $this->getId() . ' does not have a Centile Context');

        return $context->context;
    }

    public function getCentileResellerContext()
    {
        if (!$context = $this->centileContext)
            throw new MissingCentileContextException('Prestation ' . $this->getId() . ' does not have a Centile Context');

        return $context->reseller_context;
    }

    public function centileContext()
    {
        return $this->hasOne('Provisioning\CentileContext', 'prestation_id', 'prestationID');
    }

    public function setCentileContext($context)
    {
        if (!$contextDb = $this->centileContext()->first()) {
            $context = new CentileContext(['context' => $context]);
            $this->centileContext()->save($context);
        } else {
            $contextDb->context = $context;
            $contextDb->save();
        }
    }

    public function setCentileResellerContext($context)
    {
        if (!$contextDb = $this->centileContext()->first()) {
            $context = new CentileContext(['reseller_context' => $context]);
            $this->centileContext()->save($context);
        } else {
            $contextDb->reseller_context = $context;
            $contextDb->save();
        }
    }

    public function terminate($date = null)
    {
        if ($this->isTerminated())
            return;

        if ($date === null)
            $date = date('Y-m-d');

        $this->validTill = $date;

        DB::transaction(function () {
            $this->save();

            foreach ($this->dependentPrestations as $depPrestation)
                $depPrestation->terminate();

            event(new PrestationTerminated($this));
        });

        return true;
    }

    public function isActive()
    {
        if ($this->wasActivated() && ($this->validTill === null || (new \Datetime)->format('Y-m-d') < $this->validTill))
            return true;

        return false;
    }

    public function wasActivated()
    {
        if ($this->validFrom !== null && $this->validFrom <= (new \Datetime)->format('Y-m-d'))
            return true;

        return false;
    }

    public static function centileFactory(
        $type,
        $validFrom,
        $clientId,
        $groupId,
        $resellerContext,
        $context = null,
        $value = null,
        $description = null,
        $completion = self::STATUS_COMPLETION,
        $linkedWith = null)
    {
        $prestation = null;
        DB::transaction(function () use (&$prestation, $type, $validFrom, $clientId, $groupId, $resellerContext, $context, $value, $description, $completion, $linkedWith) {
            $prestation = self::create([
                'clientID' => $clientId,
                'groupID' => $groupId,
                'type' => $type,
                'supplier' => self::SUPPLIER_CENTILE,
                'valeur' => $value,
                'description' => $description,
                'validFrom' => $validFrom,
                'status' => $completion,
                'linkedWith' => $linkedWith,
            ]);

            $prestation->setCentileResellerContext($resellerContext);

            if ($context === null) {
                if ($type == CentilePrestationTypes::CENTREX)
                    $context = Enterprise::getDefaultContext($clientId, $prestation->getId());
                elseif ($type == CentilePrestationTypes::TRUNK) {
                    $context = PBXTrunking::generateTrunkName($clientId, $prestation->getId());
                }
            }
            $prestation->setCentileContext($context);

        });
        return $prestation;
    }

    public function dependentPrestations()
    {
        return $this->hasMany('Provisioning\ComptaPrestation', 'linkedWith', 'prestationID');
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeWithId($query, $id)
    {
        return $query->where('prestationID', $id);
    }
}
