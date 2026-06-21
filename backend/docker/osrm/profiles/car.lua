-- Perfil de routing OSRM para última milla urbana
-- Basado en el perfil car.lua por defecto con velocidades adaptadas
-- para entornos urbanos chilenos (Gran Valparaíso)

-- Api version
api_version = 4

-- Set name for profile
name = "car-lastmile"

-- Speed profiles for road types
-- Priority: maximum speed in km/h
speed_profile = {
    ["motorway"] = 90,
    ["motorway_link"] = 60,
    ["trunk"] = 60,
    ["trunk_link"] = 50,
    ["primary"] = 50,
    ["primary_link"] = 40,
    ["secondary"] = 40,
    ["secondary_link"] = 35,
    ["tertiary"] = 40,
    ["tertiary_link"] = 30,
    ["unclassified"] = 30,
    ["residential"] = 30,
    ["living_street"] = 15,
    ["service"] = 20,
    ["pedestrian"] = 10,
    ["track"] = 10,
    ["road"] = 30,
    ["default"] = 30
}

-- Process way to determine speed
function process_way(profile, way, result)
    local highway = way:get_value_by_key("highway")
    if highway == "" then
        return
    end

    -- Skip non-drivable highways
    local skip = {
        ["footway"] = true,
        ["bridleway"] = true,
        ["steps"] = true,
        ["cycleway"] = true,
        ["proposed"] = true,
        ["construction"] = true,
        ["elevator"] = true,
        ["busway"] = true,
        ["bus_guideway"] = true,
        ["path"] = true,
        ["corridor"] = true
    }

    if skip[highway] then
        return
    end

    -- Get speed from profile
    local speed = speed_profile[highway]
    if speed == nil then
        speed = speed_profile["default"]
    end

    -- Check maxspeed tag
    local maxspeed = way:get_value_by_key("maxspeed")
    if maxspeed ~= "" then
        local parsed = tonumber(maxspeed)
        if parsed ~= nil and parsed > 0 then
            speed = math.min(speed, parsed)
        end
    end

    -- Apply surface penalty (unpaved = slower)
    local surface = way:get_value_by_key("surface")
    if surface == "unpaved" or surface == "gravel" or surface == "dirt" or surface == "earth" then
        speed = speed * 0.6
    end

    -- Apply oneway
    local oneway = way:get_value_by_key("oneway")
    if oneway == "-1" then
        result.forward_mode = 0
        result.backward_mode = 1
    end

    -- Check access restrictions
    local access = way:get_value_by_key("access")
    if access == "private" or access == "no" then
        return
    end

    local vehicle = way:get_value_by_key("vehicle")
    if vehicle == "private" or vehicle == "no" then
        return
    end

    result.forward_speed = speed
    result.backward_speed = speed
end

-- Process turn restrictions
function process_turn(profile, turn, result)
    -- Use default turn penalties
end

-- Node processing (for traffic signals, etc.)
function process_node(profile, node, result)
    -- Use default node penalties
end

return {
    name = "car-lastmile",
    description = "Perfil adaptado para última milla urbana en Gran Valparaíso",
    api_version = 4,
    process_way = process_way,
    process_turn = process_turn,
    process_node = process_node
}
