#!/usr/bin/lua

local lfs = require("lfs")

-- Accept multiple prefixes as command-line arguments or prompt for input
local prefixes = arg[1] and arg or {}
if #prefixes == 0 then
    io.write("Enter prefixes separated by spaces: ")
    local input = io.read("*line")
    for prefix in input:gmatch("%S+") do
        table.insert(prefixes, prefix)
    end
end

local function get_pdf_pages(directory, prefixes)
    local pages = {}
    for file in lfs.dir(directory) do
        for _, prefix in ipairs(prefixes) do
            if file:match("^" .. prefix .. ".*%.pdf$") then
                local f = io.popen("pdfinfo '" .. directory .. "/" .. file .. "' | grep Pages:")
                local page_count = f:read("*all"):match("Pages:%s*(%d+)")
                f:close()
                for i = 1, tonumber(page_count) do
                    table.insert(pages, {file, i})
                end
                break  -- Move to the next file once a match is found
            end
        end
    end
    table.sort(pages, function(a, b)
        if a[1] == b[1] then
            return a[2] < b[2]
        else
            return a[1] < b[1]
        end
    end)
    return pages
end

local pages = get_pdf_pages("/home/aaa/store/tickeTrip", prefixes)

local output = io.open("/home/aaa/store/secure/texTicketCurrent/sig_set.tex", "w")
for _, page in ipairs(pages) do
    output:write(string.format("\\incpdf{%s}{%d}\n", page[1], page[2]))
    print(string.format("\\incpdf{%s}{%d}\n", page[1], page[2]))
end
output:close()

print("File '/home/aaa/store/secure/texTicketCurrent/sig_set.tex' has been created.")

print(('A bulb' .. ' rated %d hrs lasts %.1f years'):format(2200, 2200/6/365))
