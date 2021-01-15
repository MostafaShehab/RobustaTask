<?php

namespace App\Http\Controllers;

use App\Models\SeatsStop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeatsStopsController extends BaseController
{
    
    public function freeSeats(string $src, string $dst) {

        $sub_query = DB::table(DB::raw('stops as st1'))
            ->join('trips', 'trips.id', '=', 'st1.trip_id')
            ->join(DB::raw('stops as st2'), 'trips.id', '=', 'st2.trip_id')
            ->select(DB::raw('st1.order_of_stop AS o1, st2.order_of_stop AS o2, trips.id AS id'))
            ->where('st1.name', $src)
            ->where('st2.name', $dst)
            ->get();

        $start_dst_order = 0;
        $end_dst_order = 0;
        $trip = 0;
        foreach ($sub_query as $temp) {
            $start_dst_order = $temp->o1;
            $end_dst_order = $temp->o2;
            $trip = $temp->id;
        }

        $free_seats = DB::select(
            DB::raw(
                "select * from stops AS s inner join seats_stop AS ss on s.id = ss.stop_id where s.trip_id = ".$trip." AND s.order_of_stop >= ".$start_dst_order." AND s.order_of_stop < ".$end_dst_order." AND 0 = 
                    (
                        SELECT SUM(st.is_booked) 
                        FROM stops AS sx INNER JOIN seats_stop AS st INNER JOIN seats AS s1 ON st.seat_id = s1.id AND sx.id = st.stop_id
                        WHERE sx.order_of_stop >= ".$start_dst_order." AND sx.order_of_stop < ".$end_dst_order." AND sx.trip_id = ".$trip." AND ss.seat_id = st.seat_id
                    )
                    AND s.order_of_stop = ".$start_dst_order
                    )
                );
        
        foreach ($free_seats as $seat) {
            $seat->dest_order = $end_dst_order;
        }
        
        return $this->sendResponse($free_seats, 'Free seats retrieved successfully');
    }

    public function bookSeats(int $userId, int $tripId, int $src, int $dst, int $seatId) {
        
        // Validate that this user exists
        $user = DB::table('users')
                    ->where('id', $userId)
                    ->get();

                    error_log(($user));
        
        // Validate that those seats are still available
        $free = DB::table(DB::raw('seats_stop AS ss'))
                    ->join(DB::raw('stops AS s'), 'ss.stop_id', '=', 's.id')
                    ->where('ss.seat_id', $seatId)
                    ->where('s.trip_id', $tripId)
                    ->whereRaw('s.order_of_stop >= '.$src.' AND s.order_of_stop < '.$dst)
                    ->where('ss.is_booked', 0)
                    ->get();

        if(sizeof($user) == 1 and sizeof($free) == $dst - $src) {
            $affected = DB::table(DB::raw('seats_stop AS ss'))
                        ->join(DB::raw('stops AS s'), 'ss.stop_id', '=', 's.id')
                        ->where('ss.seat_id', $seatId)
                        ->where('s.trip_id', $tripId)
                        ->whereRaw('s.order_of_stop >= '.$src.' AND s.order_of_stop < '.$dst)
                        ->update(['ss.is_booked' => 1, "ss.booking_user_id" => $userId]);
        
            return $this->sendResourceCreated($affected, 'Seats booked successfully');
        } else {
            if(sizeof($user) == 1) {
                return $this->sendResourceCreatedError('Could not book seats since they are already booked');
            } else {
                return $this->sendResourceCreatedError('User not found');
            }
            
        }
        
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SeatsStop  $seatsStop
     * @return \Illuminate\Http\Response
     */
    public function show(SeatsStop $seatsStop)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SeatsStop  $seatsStop
     * @return \Illuminate\Http\Response
     */
    public function edit(SeatsStop $seatsStop)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SeatsStop  $seatsStop
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SeatsStop $seatsStop)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SeatsStop  $seatsStop
     * @return \Illuminate\Http\Response
     */
    public function destroy(SeatsStop $seatsStop)
    {
        //
    }

}
