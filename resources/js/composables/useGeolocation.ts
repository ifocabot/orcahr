import { ref } from 'vue';

export type GeoPosition = { latitude: number; longitude: number; accuracy: number };

/** Haversine distance in meters between two coordinates */
function haversineMeters(lat1: number, lng1: number, lat2: number, lng2: number): number {
    const R = 6371000;
    const toRad = (deg: number) => (deg * Math.PI) / 180;
    const dLat = toRad(lat2 - lat1);
    const dLng = toRad(lng2 - lng1);
    const a =
        Math.sin(dLat / 2) ** 2 +
        Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLng / 2) ** 2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

export function useGeolocation() {
    const position = ref<GeoPosition | null>(null);
    const error = ref<string | null>(null);
    const checking = ref(false);

    const getCurrentPosition = (): Promise<GeoPosition> => {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error('Geolocation tidak didukung perangkat ini.'));
                return;
            }
            checking.value = true;
            error.value = null;

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const geo: GeoPosition = {
                        latitude: pos.coords.latitude,
                        longitude: pos.coords.longitude,
                        accuracy: pos.coords.accuracy,
                    };
                    position.value = geo;
                    checking.value = false;
                    resolve(geo);
                },
                (err) => {
                    checking.value = false;
                    error.value = err.message;
                    reject(err);
                },
                { enableHighAccuracy: true, timeout: 10000 },
            );
        });
    };

    const checkRadius = (
        userLat: number,
        userLng: number,
        officeLat: number,
        officeLng: number,
        maxMeters: number,
    ): { withinRadius: boolean; distance: number } => {
        const distance = Math.round(haversineMeters(userLat, userLng, officeLat, officeLng));
        return { withinRadius: distance <= maxMeters, distance };
    };

    return { position, error, checking, getCurrentPosition, checkRadius };
}
