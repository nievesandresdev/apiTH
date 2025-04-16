<?php

namespace App\Services\Hoster\CloneHotel;

use App\Models\Reward;
use App\Models\RewardStay;
use Illuminate\Support\Facades\Log;

class CloneRewardsHotel
{
    public function handle($HOTEL_ID_PARENT, $HOTEL_ID_CHILD, $user)
    {
        try {
            // Clonar Rewards (referidos y referentes)
            $this->cloneRewards($HOTEL_ID_PARENT, $HOTEL_ID_CHILD, $user);

            // Clonar RewardStays
            $this->cloneRewardStays($HOTEL_ID_PARENT, $HOTEL_ID_CHILD);

            Log::info("Rewards clonados exitosamente", [
                'parent_hotel_id' => $HOTEL_ID_PARENT,
                'child_hotel_id' => $HOTEL_ID_CHILD
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Error al clonar rewards: " . $e->getMessage());
            return false;
        }
    }

    private function cloneRewards($HOTEL_ID_PARENT, $HOTEL_ID_CHILD, $user)
    {
        // Obtener todos los rewards del hotel padre
        $parentRewards = Reward::where('hotel_id', $HOTEL_ID_PARENT)->get();

        foreach ($parentRewards as $parentReward) {
            // Si el padre no tiene son_id, significa que es un nuevo reward
            if (!$parentReward->son_id) {
                // Crear nuevo reward en el hotel hijo
                $childReward = new Reward();
                $childReward->hotel_id = $HOTEL_ID_CHILD;
                $childReward->user_id = $user;
                $childReward->fill([
                    'amount' => $parentReward->amount,
                    'code' => $parentReward->code,
                    'description' => $parentReward->description,
                    'type_discount' => $parentReward->type_discount,
                    'type_rewards' => $parentReward->type_rewards,
                    'url' => $parentReward->url,
                    'enabled_url' => $parentReward->enabled_url,
                    'used' => $parentReward->used
                ]);
                $childReward->save();

                // Actualizar el padre con el ID del hijo
                $parentReward->son_id = $childReward->id;
                $parentReward->save();
            } else {
                // Si el padre ya tiene son_id, actualizar el reward hijo existente
                $childReward = Reward::find($parentReward->son_id);
                if ($childReward) {
                    $childReward->fill([
                        'amount' => $parentReward->amount,
                        'code' => $parentReward->code,
                        'description' => $parentReward->description,
                        'type_discount' => $parentReward->type_discount,
                        'type_rewards' => $parentReward->type_rewards,
                        'url' => $parentReward->url,
                        'enabled_url' => $parentReward->enabled_url,
                        'used' => $parentReward->used
                    ]);
                    $childReward->save();
                } else {
                    // Si el hijo fue eliminado, crear uno nuevo con el mismo ID
                    $childReward = new Reward();
                    $childReward->id = $parentReward->son_id;
                    $childReward->hotel_id = $HOTEL_ID_CHILD;
                    $childReward->user_id = $user;
                    $childReward->fill([
                        'amount' => $parentReward->amount,
                        'code' => $parentReward->code,
                        'description' => $parentReward->description,
                        'type_discount' => $parentReward->type_discount,
                        'type_rewards' => $parentReward->type_rewards,
                        'url' => $parentReward->url,
                        'enabled_url' => $parentReward->enabled_url,
                        'used' => $parentReward->used
                    ]);
                    $childReward->exists = false; // Forzar  el ID específico
                    $childReward->save();
                }
            }

            /* Log::info("Reward clonado/actualizado", [
                'parent_id' => $parentReward->id,
                'child_id' => $childReward->id,
                'type_rewards' => $parentReward->type_rewards
            ]); */
        }

        // Eliminar rewards del hijo que no están en el padre
        $validSonIds = Reward::where('hotel_id', $HOTEL_ID_PARENT)->pluck('son_id');
        $deletedCount = Reward::where('hotel_id', $HOTEL_ID_CHILD)
            ->whereNotIn('id', $validSonIds)
            ->delete();

        if ($deletedCount > 0) {
            Log::info("Rewards eliminados del hotel hijo", [
                'count' => $deletedCount,
                'child_hotel_id' => $HOTEL_ID_CHILD
            ]);
        }
    }

    private function cloneRewardStays($HOTEL_ID_PARENT, $HOTEL_ID_CHILD)
    {
        // Obtener todos los reward stays del hotel padre
        $parentRewardStays = RewardStay::where('hotel_id', $HOTEL_ID_PARENT)->get();

        foreach ($parentRewardStays as $parentRewardStay) {
            // Si el padre no tiene son_id, significa que es un nuevo reward stay
            if (!$parentRewardStay->son_id) {
                // Crear nuevo reward stay en el hotel hijo
                $childRewardStay = new RewardStay();
                $childRewardStay->hotel_id = $HOTEL_ID_CHILD;
                $childRewardStay->fill([
                    'code' => $parentRewardStay->code,
                    'stay_id' => $parentRewardStay->stay_id,
                    'guest_id' => $parentRewardStay->guest_id,
                    'reward_id' => $parentRewardStay->reward_id,
                    'used' => $parentRewardStay->used
                ]);
                $childRewardStay->save();

                // Actualizar el padre con el ID del hijo
                $parentRewardStay->son_id = $childRewardStay->id;
                $parentRewardStay->save();
            } else {
                // Si el padre ya tiene son_id, actualizar el reward stay hijo existente
                $childRewardStay = RewardStay::find($parentRewardStay->son_id);
                if ($childRewardStay) {
                    $childRewardStay->fill([
                        'code' => $parentRewardStay->code,
                        'stay_id' => $parentRewardStay->stay_id,
                        'guest_id' => $parentRewardStay->guest_id,
                        'reward_id' => $parentRewardStay->reward_id,
                        'used' => $parentRewardStay->used
                    ]);
                    $childRewardStay->save();
                } else {
                    // Si el hijo fue eliminado, crear uno nuevo con el mismo ID
                    $childRewardStay = new RewardStay();
                    $childRewardStay->id = $parentRewardStay->son_id;
                    $childRewardStay->hotel_id = $HOTEL_ID_CHILD;
                    $childRewardStay->fill([
                        'code' => $parentRewardStay->code,
                        'stay_id' => $parentRewardStay->stay_id,
                        'guest_id' => $parentRewardStay->guest_id,
                        'reward_id' => $parentRewardStay->reward_id,
                        'used' => $parentRewardStay->used
                    ]);
                    $childRewardStay->exists = false; // Forzar  el ID específico
                    $childRewardStay->save();
                }
            }

            /* Log::info("RewardStay clonado/actualizado", [
                'parent_id' => $parentRewardStay->id,
                'child_id' => $childRewardStay->id
            ]); */
        }

        // Eliminar reward stays del hijo que no están en el padre
        $validSonIds = RewardStay::where('hotel_id', $HOTEL_ID_PARENT)->pluck('son_id');
        $deletedCount = RewardStay::where('hotel_id', $HOTEL_ID_CHILD)
            ->whereNotIn('id', $validSonIds)
            ->delete();

        if ($deletedCount > 0) {
            Log::info("RewardStays eliminados del hotel hijo", [
                'count' => $deletedCount,
                'child_hotel_id' => $HOTEL_ID_CHILD
            ]);
        }
    }
}
