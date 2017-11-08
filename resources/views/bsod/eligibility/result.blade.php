@extends('layouts.template')

@section('page_header')
<i class='icon-ok'></i>
<span>{!!trans('bsod.eligibility.label.test_eligibility')!!}</span>
@stop

@section('page_content')

<div class='row'>
    <div class='col-sm-6 box'>
        <div class='box-header {{ $color_form }}'>
            <div class='title'>{!!trans('bsod.eligibility.label.result_eligibility')!!}</div>
        </div>
        <div class='box-content'>
            <div class="box bordered-box">
                <div class="box-content">
                    <div class='form-group'>
                        <label for="street_number_complement" class="col-md-4 control-label">{!!trans('bsod.eligibility.label.street_number')!!} {!!trans('bsod.eligibility.label.deux_points')!!} </label>
                        <div class='col-md-5 controls'>
                            {{$params['street_number']}}
                        </div>
                    </div>
                    <br />
                    <div class='form-group'>
                        <label for="street_number_complement" class="col-md-4 control-label">{!!trans('bsod.eligibility.label.complement_adresse')!!} {!!trans('bsod.eligibility.label.deux_points')!!} </label>
                        <div class='col-md-5 controls'>
                            {{$params['street_number_complement']}}
                        </div>
                    </div>
                    <br />
                    <div class='form-group'>
                        <label for="street_number_complement" class="col-md-4 control-label">{!!trans('bsod.eligibility.label.street')!!} {!!trans('bsod.eligibility.label.deux_points')!!}</label>
                        <div class='col-md-5 controls'>
                            {{$params['street']}}
                        </div>
                    </div>
                    <br />
                    <div class='form-group'>
                        <label for="street_number_complement" class="col-md-4 control-label">{!!trans('bsod.eligibility.label.zipcode')!!} {!!trans('bsod.eligibility.label.deux_points')!!}</label>
                        <div class='col-md-5 controls'>
                            {{$params['zipcode']}}
                        </div>
                    </div>
                    <br />
                     <div class='form-group'>
                        <label for="street_number_complement" class="col-md-4 control-label">{!!trans('bsod.eligibility.label.id_prise')!!} {!!trans('bsod.eligibility.label.deux_points')!!}</label>
                        <div class='col-md-5 controls'>
                            {{$params['id_prise']}}
                        </div>
                    </div>
                     <br />
                </div>
            </div>
            <table class="table table-striped table-bordered" style="margin-bottom:0;">
                <tbody>
                    @foreach($params['result'] as $key => $value)
                    @if ($key != "CodeEligibiliteTV" && $key != "CodeEligibiliteNET_VOIP")
                    <tr>
                        <td>{!!$key!!}</td>
                        @if (!is_array($value))
                        <td>{!!$value!!}</td>
                        @else
                        <td>
                            <table class="table table-striped table-bordered" style="margin-bottom:0;">
                                <tbody>
                                    @foreach ($value as $detail_key => $detail_value)
                                    <tr>
                                        <td>{!!$detail_key!!}</td>
                                        <td>{!!$detail_value!!}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                        @endif
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class='form-actions form-actions-padding' style='margin-bottom:0'>
            <div class="row text-center">
                <a href="{{URL::route('Appointment.index',[$params['eligibilityAddress_id'],$params['clientId'],'prospect'])}}" class='btn-space'>
                    <button class='btn btn-primary {{ $color_form }} btn-lg btn-space' type='submit'>
                        <i class="icon-large icon-check-sign"></i>
                        {!!trans('appointments.take_appointment')!!}
                    </button>
                </a>

            </div>
        </div>
    </div>
</div>

@stop


