<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Salaries</title>
</head>

<body>
    <form action="" method="get">
        <table border="1" style="margin-bottom: 24px">
            <tr>
                <td>Start Date</td>
                <td><input type="date" name="start_date" value="{{ $start_date }}"></td>
            </tr>
            <tr>
                <td>End Date</td>
                <td><input type="date" name="end_date" value="{{ $end_date }}"></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: right">
                    <button type="submit">Submit</button>
                </td>
            </tr>
        </table>
    </form>

    <table border="1" style="margin-bottom: 24px">
        <thead>
            <tr>
                <th>Doctor Name</th>
                <th>SIP</th>
                <th>Regular</th>
                <th>Share Regular %</th>
                <th>Share Regular</th>
                <th>Over Production</th>
                <th>Share OP %</th>
                <th>Share OP</th>
                <th>Rontgent</th>
                <th>Share Rontgent %</th>
                <th>Share Rontgent</th>
                <th>Shifts</th>
                <th>Shift Fee</th>
                <th>Total</th>
            </tr>
        </thead>

        @php
            $total_regular = 0;
            $total_overproduction = 0;
            $total_rontgent = 0;
            $total_shift_fee = 0;
            $total_share = 0;
        @endphp

        <tbody>
            @foreach ($doctors as $dx)
                @php
                    $total_regular += $dx->regular_share;
                    $total_overproduction += $dx->overproduction_share;
                    $total_rontgent += $dx->rontgent_share;
                    $total_shift_fee += $dx->shift_fee;
                    $total_share += $dx->share_total;
                @endphp

                <tr>
                    <td>{{ $dx->doctor->name }}</td>
                    @if (!is_null($dx->doctor->niptk) || $dx->doctor->niptk != '')
                        <td style="background-color: lightgreen">OK</td>
                    @else
                        <td style="background-color: lightcoral">NO</td>
                    @endif
                    <td style="text-align: right">{{ number_format($dx->regular_income, 2, ',', '.') }}</td>
                    <td style="text-align: right">{{ $dx->regular_percentage . '%' }}</td>
                    <td style="text-align: right">{{ number_format($dx->regular_share, 2, ',', '.') }}
                    </td>
                    <td style="text-align: right">{{ number_format($dx->overproduction_income, 2, ',', '.') }}</td>
                    <td style="text-align: right">{{ $dx->overproduction_percentage . '%' }}</td>
                    <td style="text-align: right">{{ number_format($dx->overproduction_share, 2, ',', '.') }}
                    </td>
                    <td style="text-align: right">{{ number_format($dx->rontgent_income, 2, ',', '.') }}</td>
                    <td style="text-align: right">{{ $dx->rontgent_percentage . '%' }}</td>
                    <td style="text-align: right">{{ number_format($dx->rontgent_share, 2, ',', '.') }}
                    </td>
                    <td style="text-align: right">{{ $dx->shifts }}</td>
                    <td style="text-align: right">{{ number_format($dx->shift_fee, 2, ',', '.') }}</td>
                    <td style="text-align: right">
                        {{ number_format($dx->share_total, 2, ',', '.') }}
                    </td>
                </tr>
            @endforeach
            <tr>
                <td colspan="4" style="text-align: right">TOTAL</td>
                <td style="text-align: right">{{ number_format($total_regular, 2, ',', '.') }}</td>
                <td style="text-align: center">---</td>
                <td style="text-align: center">---</td>
                <td style="text-align: right">{{ number_format($total_overproduction, 2, ',', '.') }}</td>
                <td style="text-align: center">---</td>
                <td style="text-align: center">---</td>
                <td style="text-align: right">{{ number_format($total_rontgent, 2, ',', '.') }}</td>
                <td style="text-align: center">---</td>
                <td style="text-align: right">{{ number_format($total_shift_fee, 2, ',', '.') }}</td>
                <td style="text-align: right">{{ number_format($total_share, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <table border="1" style="margin-bottom: 24px">
        <thead>
            <tr>
                <th>Tx ID</th>
                <th>Tx Number</th>
                <th>Type</th>
                <th>Appointment Time</th>
                <th>Svc Name</th>
                <th>Svc Income (after Disc)</th>
            </tr>
        </thead>

        @php
            $total_service = 0;
            $total_regular = 0;
            $total_rontgent = 0;
        @endphp

        <tbody>
            @foreach ($doctors as $dx)
                <tr style="background-color: aqua">
                    <td colspan="5">{{ $dx->doctor->name }}</td>
                    <td style="text-align: right">
                        {{ number_format($dx->regular_income + $dx->overproduction_income, 2, ',', '.') }}</td>
                </tr>

                @foreach ($dx->non_rontgen_transactions as $tx)
                    @php
                        $total_service += 1;
                        $total_regular += $tx->undiscount - $tx->discount;
                    @endphp

                    <tr>
                        <td>{{ $tx->id }}</td>
                        <td>{{ $tx->num }}</td>
                        <td>{{ $tx->is_overproduction == 1 ? 'OVERPRODUCTION' : 'REGULAR' }}</td>
                        <td>{{ $tx->appointment_datetime }}</td>
                        <td>
                            {{ $tx->svc_name }}</td>
                        <td style="text-align: right">
                            {{ number_format($tx->undiscount - $tx->discount, 2, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            @endforeach

            <tr style="background-color: darkgray">
                <td colspan="3" style="text-align: right">Total</td>
                <td style="text-align: right">{{ number_format($total_service, 0, ',', '.') . ' Services' }}</td>
                <td style="text-align: right">
                    {{ number_format($total_regular, 2, ',', '.') }}</td>
                <td style="text-align: right">{{ number_format($total_rontgent, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <table border="1">
        <thead>
            <tr>
                <th>Tx ID</th>
                <th>Tx Number</th>
                <th>Type</th>
                <th>Appointment Time</th>
                <th>Svc Name</th>
                <th>Svc Income (after Disc)</th>
            </tr>
        </thead>

        @php
            $total_service = 0;
            $total_regular = 0;
            $total_rontgent = 0;
        @endphp

        <tbody>
            @foreach ($doctors as $dx)
                <tr style="background-color: aqua">
                    <td colspan="5">{{ $dx->doctor->name }}</td>
                    <td style="text-align: right">{{ number_format($dx->rontgent_income, 2, ',', '.') }}</td>
                </tr>

                @foreach ($dx->rontgen_transactions as $tx)
                    @php
                        $total_service += 1;
                        $total_rontgent += $tx->undiscount - $tx->discount;
                    @endphp

                    <tr>
                        <td>{{ $tx->id }}</td>
                        <td>{{ $tx->num }}</td>
                        <td>{{ __('RONTGEN') }}</td>
                        <td>{{ $tx->appointment_datetime }}</td>
                        <td>{{ $tx->svc_name }}</td>
                        <td style="text-align: right">
                            {{ number_format($tx->undiscount - $tx->discount, 2, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            @endforeach

            <tr style="background-color: darkgray">
                <td colspan="3" style="text-align: right">Total</td>
                <td style="text-align: right">{{ number_format($total_service, 0, ',', '.') . ' Services' }}</td>
                <td style="text-align: right">
                    {{ number_format($total_regular, 2, ',', '.') }}</td>
                <td style="text-align: right">{{ number_format($total_rontgent, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
